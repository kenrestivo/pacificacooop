<?php 

//$Id$

/*
	Copyright (C) 2004  ken restivo <ken@restivo.org>
	 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details. 
	
	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once('CoopPage.php');
require_once('CoopDBDO.php');
require_once("HTML/Table.php");
require_once('object-config.php');

//////////////////////////////////////////
/////////////////////// COOP OBJECT CLASS
class coopObject
{
	var $obj;					// ref to db dataobject for this view
	var $page;					// reference to the cooppage
	var $table;					// convenience: the table the $this->obj is
	var $pk;					// name of primary key. convenience, really.
	var $id; // cache of last inserted id
	var $recurseLevel;			// level, if i'm linked from somewhere
	var $parentCO;				// reference. "parent" is reserved word
	var $backlinks;				// list of links that are linked FROM here
	var $forwardLinks;
	var $overrides = array(); 	// ugly way to customise sub-tables
    var $perms = array();       // new perms array: field=> (user=>, group=>)
    //XXX this is probaly a stupid place to store this,
    //but i can't think of where else
    var $actionnames = array('add' => 'Enter New',
                             'confirmdelete' => 'Delete',
                             'delete' => 'Delete',
                             'details' => 'Details',
                             'view' => 'View',
                             'edit'  => 'Edit');
    // used for getperms, and for report too. one place, no double-changing.
    var $permsQuery = "select 
table_permissions.table_name, table_permissions.field_name,
max(if((upriv.max_user <= table_permissions.user_level or
table_permissions.user_level is null), 
upriv.max_user, table_permissions.user_level)) as cooked_user,
max(if((upriv.max_group >=  table_permissions.group_level or
table_permissions.group_level is null), 
upriv.max_group, NULL )) as cooked_group,
max(if((upriv.max_user > table_permissions.menu_level or
table_permissions.menu_level is null),
upriv.max_user, NULL)) as cooked_menu,
max(if((upriv.max_year > table_permissions.user_level 
or table_permissions.year_level is null),
upriv.max_year, table_permissions.year_level)) as cooked_year
from table_permissions 
left join 
(select max(user_level) as max_user, max(group_level) as max_group, 
max(year_level) as max_year,
%d as user_id, realm_id
from user_privileges 
where user_id = %d 
or ((user_id < 1 or user_id is null) and group_id in 
(select group_id from users_groups_join 
where user_id = %d)) 
group by realm_id 
order by realm_id) as upriv
on upriv.realm_id = table_permissions.realm_id
where user_id = %d and table_name = '%s'
group by user_id,table_name,field_name";
    var $changes = array();  // array('field' => array('old'=>serialisedstuff, 'new'=> serialisedstuff))
    var $chosenSchoolYear = ''; // the year to use by default, as chosen by user


	function CoopObject (&$page, $table, &$parentCO, $level = 0)
		{
			if(!is_object($page) || !$table){
				PEAR::raiseError("non-page object or table ($table) passed", 
								 666);
			}

			$this->page =& $page;
			$this->table = $table ;
			$this->recurseLevel = $level;
			$this->parentCO = $parentCO;
			
			$this->page->printDebug("CoopObject: instantiating $table from $parentCO->table", 3);

 			$this->obj = CoopDBDO::factory($this->table); // & instead?
  			if (PEAR::isError($this->obj)){
				$this->page->kensPEARErrorHandler(&$this->obj);
				 user_error("coopObject::constructor: " . 
							$this->obj->getMessage(),
							E_USER_ERROR);
			}

            $this->obj->CoopObject =& $this;  //used by funcs in dbdo

			//confessObj($this->obj, "CONSTRUCTOR object for $this->table");

			$this->setPK(); // must this be after find? rather constructor.
			$this->getLinks();

			// the globals
			$this->readConf(&$GLOBALS['_DB_DATAOBJECT_FORMBUILDER']['CONFIG']);

			// read the overrides now
			$top =& $this->findTop();
			$this->page->confessArray(@$top->overrides[$this->table], 
									  "processing overrides for $this->table, top is $top->table", 3);
			// i clobber here.
			$this->readConf(&$top->overrides[$this->table], true, false);
            $this->getPerms();

		}
 


	function setPK()
		{
			$keys = $this->obj->keys();
			$this->pk = $keys[0];
			return $this->pk;
	}

	// different from the object's getlinks! this one gets my backlinks
	// it's totally different, so i can ignore problems with DBDO::getLinks
	function getLinks()
		{
			
//			confessObj($this, "view");
			global $_DB_DATAOBJECT;
			//confessObj($_DB_DATAOBJECT, "getLinks() dataobject");
			$links =& $_DB_DATAOBJECT['LINKS'][$this->obj->database()];

			$this->page->confessArray($links, 
									  "getLinks($this->table) ALL links",
									  5);

			$this->forwardLinks =& $links[$this->table];

			foreach($links as $maintable => $mainlinks){
				if(count($mainlinks)){
					foreach ($mainlinks as $nearcol => $farline){
						// split up farline and chzech it
						list($fartable, $farcol) = explode(':', $farline);
						if($fartable == $this->table){
							$this->backlinks[$maintable] = $nearcol;
						}
					}
				}
			}

			$this->page->confessArray($this->backlinks,
									  "getLinks() backlinks for $this->table",
									  4);

			$this->page->confessArray($this->forwardLinks,
									  "getLinks() forward for $this->table",
									  4);
			return $this->backlinks;
		}

	function isLinkField($key)
		{

			// and only if, um, the links.ini agrees that they are there
			if(!$this->forwardLinks){
				//print "no links for $this->table $key $val<br>";
				return false;
			}
			$this->page->confessArray($this->forwardLinks, 
									  "isLInkField($key): links for $this->table", 4);

			if(empty($this->forwardLinks[$key])){
				return false;
			}
			
			
			return true;

		}


	//  inspired by formbuilder's getdataobjctselectdisplayvalue (whew!)
	function checkLinkField($key, $val)
		{
		
			if(!$this->isLinkField($key)){
				$this->page->printDebug("$this->table $key is not a linkfield",
										4);
				return $val;
			}

			// don't bother with blanks either
			if(!$this->obj->$key){
				return false;
			}

			//ok, we have run the fucking gauntlet here.
			//confessObj($obj, 
//					   "checkLinkField() from $this->table: obj with links for $key of $val");
            list($subtable, $subkey) = explode(':', $this->forwardLinks[$key]);
			$sub =& new CoopObject(&$this->page, $subtable, &$this);
            $sub->obj->get($subkey, $this->obj->$key);
            

	//confessObj($subobj, "checkLInkFild() subobj $subobj->__table for $key of $val");


			// remember, subobj does NOT have a concatlinkfields method
			// so i am keepign the method here and passing the subobj
			return $sub->concatLinkFields();
		}


    // NOTE! i pass the DBDO object in because getlinks DOES NOT KNOW how
    // to get my coopobject objects. duh.
	function concatLinkFields()
		{
			$ldfs = $this->obj->fb_linkDisplayFields;
			if(!$ldfs){
				return $val;
			}
			//confessObj($this->obj, "concatlinkfields(obj)");
			// only if i have linkfields in the dataobj
			$val = false; 		// gotta reset it here.
			foreach($ldfs as $linkfield){
				// trying to YAGNI here. i don't need 2-level links yet
				// so, i'm not coding that recursion in here now. sorry charlie.
				if($this->obj->$linkfield){
					// this is where i'd recursively czech these.
					// also, building an array and then imploding it
					// might be more readable
                    // TODO: make sure i don't show the - if nothing there
                    // ALSO TODO: do the formatting i.e. coopview toArray!!
					$val .= sprintf("%s%s", $val ? ' - ' : "", 
									$this->checkLinkField($linkfield, 
                                                          $this->obj->$linkfield));
				}
			}
			return $val;
		}

	// returns the IMMEDIATE parent, not including joins
	function &getParent()
		{
			// XXX hack to skip over jointables. ugly, but it works.
			if(preg_match('/_join/', $this->parentCO->table, $matches)){
				return $this->parentCO->getParent();
			}
			//confessObj($view, "companyDetails(view)");
			return $this->parentCO;

		}

	// nice recursive function. returns summary of parents, skipping joins
	// note now this is *different* from getParent.. don't try to combine em
	function getSummary()
		{
			
			//confessObj($this, 'getSummary');
			return $this->concatLinkFields(&$this->obj);
				
		}

    function lastInsertID()
        {
            $db =& $this->obj->getDatabaseConnection();

            $data =& $db->getOne('select last_insert_id()');
            if (DB::isError($data)) {
                die($data->getMessage());
            }
            return $data;
        }

	// recurses through parents, until it finds the top!
	function &findTop()
		{
			if(is_object($this->parentCO) && 
			   is_a($this->parentCO, 'CoopObject'))
			{
				return $this->parentCO->findTop();
			}
			return($this);
		}

	function saveAudit($insert = true)
		{
			// don't save audits for joins dude
			if(ereg("_join", $this->table)){
				return;
			}
			
			// the audit trail dude
			// TODO copy/paste this later into my framework
			$aud =& new CoopObject(&$this->page, 'audit_trail', &$top);
			$aud->obj->table_name = $this->table;


            // NOTE. the coopform insert palready populated $this->id
            $aud->obj->index_id = $this->id;

			
			// one more sanity czech
			if(!$aud->obj->index_id){
				PEAR::raiseError('NULL audit trail attempt', 666);
			}

            // serialise and save the details!
            if(count($this->changes)){
                $aud->obj->details = serialize($this->changes);
            }

            $aud->obj->updated = date('Y-m-d H:i:s');

			$aud->obj->audit_user_id = $this->page->auth['uid'];

            //$aud->debugWrap(2);
			$aud->obj->insert();
            
            $this->triggerNotices($this->lastInsertID());

		}
    
    // key is the field. leave blank to get ispermittedRECORD, basically.
    //forceuser means assume the record belongs to this user even if it does not (useful in menus, or where you want a best case situation)
    // forceyear is to assume that the record is this year's-- best case, i.e. in menus
	function isPermittedField($key = NULL, $forceuser = false, 
                              $forceyear = null)
		{

			// if it's a key, and we don't show them, then no
			if($key == $this->pk && $this->obj->fb_hidePrimaryKey){
				$this->page->printDebug("$this->table : $key is a pk", 4);
				return false;
			}
			//we don't show if not in fieldstorender
			//NOTE: it could not be in the db itself,
			//but if it's in fieldstorender, then we show it anyway
			if(!empty($this->obj->fb_fieldsToRender) && $key &&
			   !in_array($key, $this->obj->fb_fieldsToRender))
            {
				$this->page->printDebug(
                    "ispermitted($this->table : $key) NOT in fieldstorender", 
                    4);
				return false;
			}
            

            if(!empty($this->obj->fb_fieldsToUnRender)  &&
			   in_array($key, $this->obj->fb_fieldsToUnRender))
            {
            	$this->page->printDebug(
                    "ispermitted($this->table : $key) is in UNrender, so blocking", 
                    4);
			    return false;  // i am very, very sorry for this
            }


            // i'm looking in perms calc. choose what to use now.
            // remember! the db needs to give separate perms for fields/tables
            $usethese = isset($this->perms[$key]) ? 
                $this->perms[$key] : $this->perms[NULL];



            if($key != 'family_id' && ($forceuser || 
               (!empty($this->obj->family_id) && 
                $this->page->userStruct['family_id'] == $this->obj->family_id)))
            {
            	$this->page->printDebug(
                    "ispermitted($this->table : $key) MINE, using max of group/user", 
                    4);
                $res =  max($usethese['user'], 
                           $usethese['group']);
            } else {
                $res = $usethese['group'];
            }

            /// decide if this is ANOTHER year
            if(!$forceyear &&  
               (($this->inObject('school_year') &&
                 $this->page->currentSchoolYear != $this->obj->school_year))
               || $key == 'school_year')
            {
                
            	$this->page->printDebug(
                    "ispermitted($this->table : $key) {$this->obj->school_year} is NOT {$this->page->currentSchoolYear}, so limiting for permcheck", 
                    4);
                //PEAR::raiseError('foobar', 111);
                    //MIN! limit them.
                $res = min($res, $usethese['year']);
            }




            $this->page->printDebug(
                sprintf('ispermitted(%s : %s) RETURNING for OBJ famid [%s] , my famid [%d], force user [%s] force year [%s] perms [%s]', 
                        $this->table, $key, empty($this->obj->family_id) ? 
                        'NO FAMILY ID IN OBJECT' : $this->obj->family_id, 
                        $this->page->userStruct['family_id'], 
                        $forceuser, $forceyear, $res),
                4);
             
            return $res;

		}

	// so commonly used, i need it here
	// gets the crosslinks FOR THIS OBJECT
	// the index MUST NOT be null.
	function checkCrossLinks($midTable, $farTable)
		{
 			if($this->obj->{$this->pk} < 1){
 				return array();
 			}

			$included = array(); // must return array even if empty!
			// TODO: maybe try to calculate mid or fartable? dangerous?
			$far = new CoopObject(&$this->page, $farTable, $this);
			//confessObj($this, 'CoopObject::checkCrossLinks(this)');
			$this->debugWrap(2);
			$mid =& new CoopObject(&$this->page, $midTable, &$far);
			$mid->obj->{$this->pk} = $this->obj->{$this->pk};
			$far->obj->joinAdd($mid->obj);
			$far->obj->orderBy(sprintf("%s.%s", $far->table, $far->pk));
			if($far->obj->find()){
				$included = array();
				while($far->obj->fetch()){
					$included[] = $far->obj->{$far->pk};
				}
			}
			return $included;
		}

	function title()
		{
			if($this->obj->fb_formHeaderText){ 
				return $this->obj->fb_formHeaderText;
			}
			return ucwords($this->table);
		}
	
	// by default, i want to constrain join/finds in this way:
	// this callback is executed BEFORE stuffing choices in a SELECT box
	// it also does the orderBy stuff. this function is insanely important
	function linkConstraints()
		{

            if(is_callable(array($this->obj, 'fb_linkConstraints'))){
                return $this->obj->fb_linkConstraints(&$this);
            } 

            
// XXX  you goddamned better have a path to family, everywhere
// unless you have permissions to see all
            if($this->inObject('family_id')){
                $this->constrainFamily();
            }
              
            // XXX instead, i need to  check  fb_joinpaths!!!
            // there may not be a DIRECT path to schoolyear,
            //but i need to constrain anyway!
            if($this->inObject('school_year')){
                $this->constrainSchoolYear();
            }
             
             
             if(!empty($this->obj->fb_linkDisplayFields) && 
                count($this->obj->fb_linkDisplayFields) > 0)
             {
                 $this->obj->orderBy(
                     implode(',', 
                             array_map(
                                 create_function(
                                     '$item',
                                     "return(sprintf('%s.%s', '{$this->table}', \$item));"),
                                 $this->obj->fb_linkDisplayFields)));
             }

		}


    function constrainSchoolYear($force = false)
        {
            
            if(!empty($this->obj->fb_joinPaths['school_year'])){
                $paths = explode(':', $this->obj->fb_joinPaths['school_year']);
                $last = array_pop($paths);
                $this->page->printDebug("CoopObject::constrainSchoolYear({$this->table}) final path is $last", 2);
            }

            
            //this code ought to be taken out and shot.
            // XXX for starters, use ispermittedfied, not perms[null]
            if($this->perms[null]['year'] < ACCESS_VIEW || $force) {
                $this->page->printDebug("CoopObject::constrainSchoolYear({$this->table})", 2);
                //TODO: search up the link heirarchy to find where school year!
                $this->obj->whereAdd(
                    sprintf('%sschool_year = "%s"',
                            empty($last) ? '' :  $last . '.' ,
                            $this->page->currentSchoolYear));
            } else {
                $chosen = $this->getChosenSchoolYear();
                // yes, i only constrain IFF something is chosen
                // so that  linkfields properly show all years
                if($chosen){
                    $this->obj->whereAdd(
                        sprintf('(%sschool_year like "%s" or %sschool_year is null or %sschool_year < "1900-01-01")', 
                                empty($last) ? '' :  $last . '.' ,
                                $chosen,
                                empty($last) ? '' :  $last . '.' ,
                                empty($last) ? '' :  $last . '.' ));
                    $this->obj->orderBy('school_year desc');
                }
            }
        }



    function constrainFamily($force = false)
        {
            if(($this->isPermittedField(null, false, true) < ACCESS_VIEW  || 
                $force) &&
               $this->page->userStruct['family_id'])
            {
                $this->page->printDebug("FORCING familyid for search, with wheraedd", 2);

                //TODO: i'm going to have to specify the table here,
                //using joinpath, as i do in constrainschoolyear
                $this->obj->whereAdd(
                    sprintf('family_id = %d',
                            $this->page->userStruct['family_id']));
            }
            
        }


	/// XXX this is not really used. instead, i overload insert
	function getCounter($column, $schoolyear = false)
		{
			if(!$schoolyear){
				$schoolyear  = $this->page->currentSchoolYear;
			}
			$this->obj->query("update counters set 
						counter = last_insert_id(counter+1) 
				where column_name='$column' and school_year = $schoolyear");
			return $this->lastInsertID();
		}

	// prepends the name fo this table to a field, returns the new long name
	// used in coopview and in coopform, for all non-legacy new-style pages
	function prependTable($col)
		{
			return sprintf('%s-%s', $this->table , $col);
		}


	// lifted from FB. i need some of this stuff.
	function readConf(&$array, $clobber = false, $fbify = true)
		{
			$vars = get_object_vars($this->obj);
			if (is_array($array)) {
				//read all config options into member vars
				foreach ($array as $key => $value) {
					//  loathe FormBuilder
					$fbkey = $fbify  ?  "fb_$key" : $key;	
					if ((in_array($key, $vars) && !isset($this->obj->$fbkey)) ||
						$clobber) 
					{
						$this->page->printDebug(
							 "$this->table $fbkey being set to $value", 
							 4);
						$this->obj->$fbkey = $value;
					}
				}
			}
			
		}

    //populate permissions array, which caches this stuff
    function getPerms()
        {
            //it's REALLY annoying to see that shit here.
            $this->debugWrap(7);
            $this->obj->query(sprintf($this->permsQuery,
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->table));
            $res = $this->obj->getDatabaseResult();
            while ($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                // chasing bthe heisenbug
               $this->page->confessArray(
                   $row, "getPERMS({$this->table}) db outfreakage", 5);
                $this->perms[$row['field_name']] = 
                    array('user'=>$row['cooked_user'],
                          'group' =>$row['cooked_group'],
                          'menu' =>$row['cooked_menu'],
                          'year' =>$row['cooked_year']);
            }

            $this->page->confessArray($this->perms, 
                                      "getPerms({$this->table}) foun in db", 2);


        }



    // populates the jointable ONLY for tables not already set by dbdo
    // ONLY a few will have this be dynamic. most have it static for now.
    function joinTo($fieldname)
        {
            // it's local, fret not
            if(in_array($fieldname, array_keys(get_class_vars($this->table)))){
                $this->obj->fb_joinPaths[$fieldname] = $this->table;
            }
            if(!isset($this->obj->fb_joinPaths[$fieldname])){
                return false;
            }

        }

    function debugWrap($pagedebuglevel, $dblevel = 1)
        {
            if($this->page->debug < $pagedebuglevel){
                return;
            }

            $this->page->printDebug("$this->table setting dbdo debuglevel to $dblevel");
            $this->obj->debugLevel($dblevel);
        }

    // very useful utility for checking for the presence of a field
    // NOTE: if  'class', it could use obj->table() instead of get_class_vars
    function inObject($key, $type = 'object', $isset = false)
        {
            if($isset){
                return isset($this->obj->$key);
            }
  
            $res =in_array($key, 
                            array_keys(call_user_func("get_{$type}_vars",
                                                      $this->obj)));

            // i get OBJECT vars here, not class vars. if i've set it
            $this->page->confessArray(array_keys(get_object_vars($this->obj)), 
                         "inobject($key, $type, $isset) in $this->table = $res", 
                                      4);
            //shows whether this thing is part of this object!
             return $res;
        }

    function doJoins()
        {
            if(empty($this->obj->fb_joinPaths)){
                return;
            }

            // each LINKID/PATH
            foreach ($this->obj->fb_joinPaths as $key => $path){
                // TODO: this is a stub. change debuglevel here to 4 when done
                $this->page->printDebug("joining {$this->table} path to $key", 
                                        5);
                $this->recurseJoin($path);
            }

        }
    
    function recurseJoin($stack)
        {
            /// TODO: do something!!
        }



    function reorder($things)
        {
            if(empty($this->obj->fb_fieldLabels)){
                return $things;
            }

            foreach(array_keys($this->obj->fb_fieldLabels) as $key){
                isset($things[$key]) && $sorted[$key] = $things[$key];
            }
            return $sorted;
        }


    // returns key->val pair, useful for QF selectboxes
    // this is in here, not in QF, because i will need it elsewhere too
	function getSchoolYears($val = false, $all = false)
		{
  
            // the actual work for *this* table

            $db =& $this->obj->getDatabaseConnection();

            $years = $db->getCol(
                sprintf('select distinct school_year from %s 
                        group by school_year order by school_year', 
                        //XXX we always use enrollment to get schoolyears!
                        'enrollment'),
                'school_year');

            $this->page->confessArray($years, 'getschoolyears', 5);
  
            if(!is_array($years) || !in_array($this->page->currentSchoolYear,
                                              $years))
            {
                array_push($years, $this->page->currentSchoolYear);
            }

            $next = findSchoolYear(0,1,1);
            if(!in_array($next, $years))
            {
                array_push($years, $next);
            }

            asort($years);
        
            // for the various coopview choosers, not for saving
            if($all){
                $options['%'] ='ALL';
            }

 
            // array_push($options, array_combine($years, $years)) 
                //is only in PHP5. doy.
            foreach($years as $year){
                $options[$year] = $year;
            }

            return $options;
		}

    // gets all links, forward and backward,
    // as array: tablename => array(nearid, farid)
    function allLinks()
        {
            foreach(array_merge($this->backlinks, $this->forwardLinks) 
                    as $key =>$val)
            {
                
                // backlinks and forward links are different, alas
                if(strstr($val, ':')){
                    $nearid = $key;
                    list($table, $farid) = explode(':', $val);
                } else {
                    $table = $key;
                    $nearid = $farid = $val;
                }
                
                if($table == $this->table){ // blow off recursion
                    continue;
                }
                
                $all[$table] = array($nearid, $farid);
   
            }
            $this->page->confessArray($all, "allLinks for {$this->table}", 4);
            return $all;
        }


    // TODO: this belongs in coopview. really. go put it back!
    function getChosenSchoolYear($orcurrent = false)
        {
            $this->page->printDebug("CoopObject::getChosenSchoolYear({$this->table})", 
                                    3);
            if($this->chosenSchoolYear){
                $this->page->printDebug(
                    "getChosenSchoolyear {$this->table} found {$this->chosenSchoolYear}", 
                    3);
                return $this->chosenSchoolYear;
            } 

            $top =& $this->findTop();
            if(!$this->isTop(&$top)){
                return $top->getChosenSchoolYear();
            }
            
            return $this->page->currentSchoolYear;
        }

function triggerNotices($audit_id)
        {
            //XXX fix this path!
            $parth = parse_url($_SERVER['PHP_SELF']);
            $inner = pathinfo($parth['path']);
//             $this->page->confessArray($parth, 'parth', 4);
//             $this->page->confessArray($inner, 'inner', 4);
            $host = $_SERVER['SERVER_NAME'];

            $fp = fsockopen($host, 80, $errno, $errstr, 1);
            if (!$fp) {
                $this->page->printDebug("$errstr ($errno)", 1);
                $this->page->mailError('SUBSCRIPTION TRIGGER FAILED ON LIVE SITE!', 
                                       "ERRNO $errno, ERRSTR $errstr");
            } else {
                fputs ($fp, "GET {$inner['dirname']}/send_email.php?audit_id={$audit_id} HTTP/1.0\r\nHost: {$host}\r\n\r\n");
                fclose ($fp);
            }

        }


    function isTop($top = false)
        {
            if(!$top){
                $top =& $this->findTop();
            }
            //XXX HACK! can't compare objects. so instead compare table name!
          return $top->table == $this->table;
        }

    // joinobj is a  ccopobject object! 
    // you'll need this to make linkconstraints coexist with extradetails
    // XXX it checks interal DBDO variables like _join. DANGEROUS!
    function protectedJoin(&$joinco, $jointype = 'left')
        {

            if(strstr($this->obj->_join, $joinco->table)) {
                $this->page->printDebug(
                    sprintf('protectedJoin(%s) already contains join for %s, skipping',
                            $this->table, 
                            $joinco->table),
                    2);
                return;
            }
            $this->page->confessArray(
                $this->obj->_join, 
                "CoopObject:protectedJoin({$this->table}) joins, before joining {$joinco->table}", 
                1);


            $this->obj->joinAdd($joinco->obj, $jointype);
            $this->page->confessArray(
                $this->obj->_join, 
                "CoopObject:protectedJoin({$this->table}) joins, AFTER joining {$joinco->table}", 
                1);
        }

    // this is used by various linkdisplay-type summaries, i.e. JSON and popups
    function &getData($query, $limit, $beginsWith)
        {
            if(strlen($query)< 2){
                return array();
            }
            foreach($this->obj->fb_linkDisplayFields as $ldf){
                $this->obj->whereAdd(sprintf('%s like "%s%s%%"',
                                             $ldf, 
                                             $beginsWith ? '' : '%',
                                             $query), 
                                     'OR');
            }
            

            $this->linkConstraints();
            
            //$this->debugWrap(2);
			$this->obj->find();
			while($this->obj->fetch()){
				$options[(string)$this->obj->{$this->pk}] =
 $this->concatLinkFields();
			}
            
            $this->page->confessArray($options, 'getData options', 4);
            return $options;
        }
    
    
    //used in coopform customselectboxes mostly
    // returns targettable and field
    function getLink($field)
        {
            if(isset($this->forwardLinks[$field])){
                $link =$this->forwardLinks[$field];
                return explode(':', $link);
            } 
            
            //XXX is this even necessary? is it daaangerous???
            $flipped = array_flip($this->backLinks);
            if(isset($flipped[$field])){
                return array($flipped[$field], $field);
            } 
            
        }


    function getLinkField($table)
        {
            foreach ($this->forwardLinks as $nearfield => $far){
                list($fartable, $farfield) = explode(':', $far);
                if($fartable == $table){
                    return $nearfield;
                }
            }

        }


    function getInstructions($action = false)
        {
            if(!$action){
                $this->page->mailError('get instructions called with no action');
                return;
            }
            $inst = $this->obj->factory('instructions');
            $inst->table_name = $this->table;
            
            $inst->action = $action;
            $found = $inst->find(true);
            if($found < 1){
                return ;
            }
            return '<p class="instructions">' . $inst->instruction . '</p>';
            }
    
} // END COOP OBJECT CLASS


////KEEP EVERTHANG BELOW

?>