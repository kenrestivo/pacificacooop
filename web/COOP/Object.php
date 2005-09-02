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
require_once('DB/DataObject.php');
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

 			$this->obj = DB_DataObject::factory($this->table); // & instead?
  			if (PEAR::isError($this->obj)){
				$this->page->kensPEARErrorHandler(&$this->obj);
				 user_error("coopObject::constructor: " . 
							$this->obj->getMessage(),
							E_USER_ERROR);
			}
			//confessObj($this->obj, "CONSTRUCTOR object for $this->table");

			$this->setPK(); // must this be after find? rather constructor.
			$this->getLinks();

			// the globals
			$this->readConf(&$GLOBALS['_DB_DATAOBJECT_FORMBUILDER']['CONFIG']);

			// read the overrides now
			$top =& $this->findTop();
			$this->page->confessArray($top->overrides[$this->table], 
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
									  4);

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

			if(!($this->forwardLinks && $this->forwardLinks[$key])){
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

			$subobj = $this->obj->getLink($key); 
	//confessObj($subobj, "checkLInkFild() subobj $subobj->__table for $key of $val");


			// remember, subobj does NOT have a concatlinkfields method
			// so i am keepign the method here and passing the subobj
			return $this->concatLinkFields(&$subobj);
		}



	function concatLinkFields(&$obj)
		{
			$ldfs = $obj->fb_linkDisplayFields;
			if(!$ldfs){
				return $val;
			}
			//confessObj($obj, "concatlinkfields(obj)");
			// only if i have linkfields in the dataobj
			$val = false; 		// gotta reset it here.
			foreach($ldfs as $linkfield){
				// trying to YAGNI here. i don't need 2-level links yet
				// so, i'm not coding that recursion in here now. sorry charlie.
				if($obj->$linkfield){
					// TODO: this is where i'd recursively czech these.
					// also, building an array and then imploding it
					// might be more readable
					$val .= sprintf("%s%s", $val ? ' - ' : "", 
									$obj->$linkfield);
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
			
			// XXX hack to skip over jointables. ugly, but it works.
			if(preg_match('/_join/', $this->table, $matches)){
				return $this->parentCO->getSummary();
			}
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
            $this->id = $data;
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

			if($insert){
				// NO CONFIDENcE  in DBDO. use lastinsert instead!
// 				if($this->obj->{$this->pk} != $this->lastInsertID()){
// 					user_error("last insert != object's PK! this shouldn't happen",
// 							   E_USER_ERROR);
// 				}
                // NOTE. the coopform insert palready populated $this->id
				$aud->obj->index_id = $this->id;
			} else {
				$aud->obj->index_id = $this->obj->{$this->pk};
			}
			
			// one more sanity czech
			if(!$aud->obj->index_id){
				PEAR::raiseError('NULL audit trail attempt', 666);
			}

			$aud->obj->audit_user_id = $this->page->auth['uid'];
			$aud->obj->insert();
		}


	function isPermittedField($key = NULL, $forceuser = false)
		{

			// if it's a key, and we don't show them, then no
			if($key == $this->pk && $this->obj->fb_hidePrimaryKey){
				$this->page->printDebug("$this->table : $key is a pk", 4);
				return false;
			}
			//we don't show if not in fieldstorender
			//NOTE: it could not be in the db itself,
			//but if it's in fieldstorender, then we show it anyway
			if($this->obj->fb_fieldsToRender && $key &&
			   !in_array($key, $this->obj->fb_fieldsToRender))
            {
				$this->page->printDebug(
                    "ispermitted($this->table : $key) NOT in fieldstorender", 
                    4);
				return false;
			}
			

            if(is_array($this->obj->fb_fieldsToUnRender)  &&
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


            if($forceuser || 
               $this->page->userStruct['family_id'] == $this->obj->family_id)
            {
            	$this->page->printDebug(
                    "ispermitted($this->table : $key) MINE, using max of group/user", 
                    4);
                $res =  max($usethese['user'], 
                           $usethese['group']);
            } else {
                $res = $usethese['group'];
            }

            $this->page->printDebug(
                "ispermitted($this->table : $key) RETURNING for OBJ famid {$this->obj->family_id}, my famid {$this->page->userStruct['family_id']}, force [$forceuser] perms [$res]",
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
			$this->page->debug > 2 && $far->obj->debugLevel(2);
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
	
	// by default, i want to constrain finds in this way:
	function defaultConstraints()
		{
			$this->obj->school_year = findSchoolYear();
		}

	/// XXX this is not really used. instead, i overload insert
	function getCounter($column, $schoolyear = false)
		{
			if(!$schoolyear){
				$schoolyear  = findSchoolYear();
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
            $this->obj->debugLevel($this->page->debug > 7 ? 2 : 0);
            $this->obj->query(sprintf("
select 
table_permissions.table_name, table_permissions.field_name,
max(if((upriv.max_user <= table_permissions.user_level or
table_permissions.user_level is null), 
upriv.max_user, table_permissions.user_level)) as cooked_user,
max(if((upriv.max_group >=  table_permissions.group_level or
table_permissions.group_level is null), 
upriv.max_group, NULL )) as cooked_group,
max(if((upriv.max_user > table_permissions.menu_level or
table_permissions.menu_level is null),
upriv.max_user, NULL)) as cooked_menu
from table_permissions 
left join 
(select max(user_level) as max_user, max(group_level) as max_group, 
%d as user_id, realm_id
from user_privileges 
where user_id = %d 
or (user_id is null and group_id in 
(select group_id from users_groups_join 
where user_id = %d)) 
group by realm_id 
order by realm_id) as upriv
on upriv.realm_id = table_permissions.realm_id
where user_id = %d and table_name = '%s'
group by user_id,table_name,field_name
        ",
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->table));
            $res = $this->obj->getDatabaseResult();
            while ($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                // chasing bthe heisenbug
               $this->page->confessArray($row, 
                                          "getPERMS({$this->table}) db outfreakage", 5);
                $this->perms[$row['field_name']] = 
                    array('user'=>$row['cooked_user'],
                          'group' =>$row['cooked_group'],
                          'menu' =>$row['cooked_menu']);
            }

            $this->page->confessArray($this->perms, "getPerms({$this->table}) foun in db", 2);

            //XXXX: should these defaults NOT BE HARDCODED HERE?
            /// stick these in the db instead
            if(count($this->perms) < 1){
                $this->page->printDebug("getperms({$this->table}): NO perms, inserting defaults", 4);
                $this->perms[null]['user'] = ACCESS_DELETE;   
                $this->perms[null]['group'] = ACCESS_VIEW;   
                $this->perms[null]['menu'] = $this->perms[null]['user'];   
            }

        }


    // because it's breadth first, returns an array
    function findPathToField($field, $path = '')
        {
            // easier if i append right at the top
            $path = $path ? "$path:". $this->table : $this->table;
            $this->page->printDebug("CoopObject::findpathtofield($field, $path): starting", 4);

            $res = array();
            $searchthese = array();

            // first off, if i'm the one, i'm done!
            if(in_array($field, array_keys(get_class_vars($this->table)))){
                $res[] = $path;
                $this->page->confessArray(get_class_vars($this->table), 
                                          "CoopObject::findpathtofield($field, $path) found in top-level", 5);
                return $res;
            }

            //breadth first
            foreach($this->backlinks as $fartable => $farfield){
                $co =& new CoopObject(&$this->page, $fartable, &$this);
                $tmp = $co->findPathToField($field, $path);
                if(count($tmp) > 0){
                     $this->page->printDebug("CoopObject::findpathtofield($field, $path) found it in $fartable", 
                                             4);
                    $res = array_merge($res, $tmp);
                } else {
                    //deal with later, remember, breadth first
                    $searchthese[] =& $co;
                }
            }
            
            if(count($res)){
                // first-breadth worked. close enough for rock and roll
                $this->page->printDebug("CoopObject::findpathtofield($field, $path) first pass worked", 4);
                return $res;
            }
        
            // nothing found at top, so depth-charge me
            if(count($searchthese)){
                foreach($searchthese as $co){
                    $tmp = $co->findPathToField($field, $path);
                    if(count($tmp)){
                        $res = array_merge($res, $tmp);
                    }
                }
            }
            return $res;
        }


    function joinTo($fieldname)
        {
            if(!isset($this->obj->joinPaths[$fieldname])){
                $found = $this->findPathToField($fieldname);
                if(count($found) > 1){
                    $this->page->confessArray($found, "joinTo($fieldname): WARNING! taking first one", 
                                              1);
                }
                count($found) && $this->obj->joinPaths[$fieldname] = $found[0]; 
            }
        }


} // END COOP OBJECT CLASS


////KEEP EVERTHANG BELOW

?>