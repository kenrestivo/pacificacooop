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

require_once('CoopObject.php');
require_once('DB/DataObject.php');
require_once("HTML/Table.php");
require_once('object-config.php');

//////////////////////////////////////////
/////////////////////// COOP VIEW CLASS
class coopView extends CoopObject
{

	var $legacyCallbacks;			// hack for old callbacks
	var $legacyPerms; 			// cache of OLD-style permissions ($p)
	var $extraRecordButtons;  // HACK for non-standard actions, i.e. thankyous
    var $fullText;             // NASTY hack to avoid truncating text on details
    var $recordActions = array('edit'=> ACCESS_EDIT, 
                               'confirmdelete' => ACCESS_DELETE, 
                               'details' => ACCESS_VIEW);      
    var $viewActions = array('add' => ACCESS_ADD, 
                             'view'=> ACCESS_VIEW);     
    var $searchForm; // cache of search form interface


    //chain up
	function CoopView (&$page, $table, &$parentCO, $level = 0)
		{
			parent::CoopObject(&$page, $table, &$parentCO, $level);
            $this->obj->CoopView =& $this;  //used by funcs in dbdo

            $this->joinTo('family_id');
            $this->joinTo('school_year');
            $page->confessArray(&$this->obj->fb_joinPaths,
                                      'coopView() joinpaths found', 3);
            // reset debuglevel in obj, which may bave been set by save!
            $this->debugWrap(5);

            $this->doJoins();
            
		}



	// i will need the old-skool callbacks and perms
	// in order to use the old-skool buttons and actions calculations
	// when i have new-skool button and dispatcher, this will be depreciated
	function createLegacy($callbacks)
		{
			
			// sanity check to ease debugging
			if(!is_array($callbacks)){
				user_error("CoopView:createLegacy(): callbacks not an array", 
						   E_USER_ERROR);
			}

		// TODO guess if not provided? look thru indexedeverything
			$this->legacyCallbacks = $callbacks; 
		  
			//  calculate always! it keeps realms straight
			$perms = getAuthLevel($this->page->auth, 
								  $this->legacyCallbacks['realm']);
	
			$this->legacyPerms = $perms;
	
		}

	// formats object is current in this object, um, as a table
	function simpletable($find= true)
		{
            $rowcnt = 0;
            
			// NOTE. this object's find, not the DBDO find
			if(!$this->find($find)){
				return;
			}

			$tab = new HTML_Table();	
			while($this->obj->fetch()){
				if(!$rowcnt++){
					// MUST do this *after* fetch, to handl custom queries
					// where column names are different from what's in obj
                    $header = $this->makeHeader();
                    $this->page->confessArray($header, 'header', 2);
					$tab->addRow(array_values($header['titles']), 
							 'class="tableheaders"', 'TH'); 
                }
				//$tab->addRow(array_values($this->obj->toArray()));
                if($this->isPermittedField() < ACCESS_VIEW){
                    $this->page->printDebug(
                        sprintf('simpletable ERROR WARNING! row %d of %s is not permitted, but it showed up in the results! your whereadd is out of sync with ispermittedfield', 
                                $this->obj->{$this->pk}, 
                                $this->table ));
                        continue;
                }
                $tab->addRow($this->toArray($header['keys']),
                             'valign="top"');
                			
			}
			
			$tab->altRowAttributes(1, 'class="altrow1"', 
								   'class="altrow2"');

			
			return $this->tableTitle($tab->toHTML());

		}

	function horizTable($find = true)
		{
			// NOTE. this object's find, not the DBDO find
			if(!$this->find($find)){
				return;
			}

			$tab =& new HTML_Table();
            $header = $this->makeHeader();
			$tab->addCol($header['titles'], 'align=right', 'TH');

			while($this->obj->fetch()){
				$tab->addCol($this->toArray($header['keys']),
                             'class="tabletitles"' );
			
			}
			if($this->extraRecordButtons){
				$tab->addRow(array("", $this->extraRecordButtons));
			}

			return $this->tableTitle($tab->toHTML());
	
		}
	
    // the $find arg is whether to actually do the object find.
    // note that it's default false (assuming the caller found already),
    // BUT most of its callers default to true, so... it's ugly
	function find($find = false)
		{


            // i stuck this into find so i don't have to duplicate it
            // in horiztable, simpletable, onelinetable, etc
            // and i have to FORCE ispermitted to return the userlevel
            $perms = $this->isPermittedField(NULL,true,true);

            if($perms < ACCESS_VIEW){
                $this->page->printDebug(
                    "CoopView::find($this->table) NO VIEW PERMS!", 2 );
                return false;
            }
            
            $this->schoolYearChooser();

            
            $this->linkConstraints();
	
            $this->debugWrap(5);

            //// finally, go get 'em!
            if($find){
				$found = $this->obj->find();
			} else {
				$found = $this->obj->N;
			}

            $this->page->printDebug("CoopView::find($find) $this->table found $found", 2);
            // ALSO. if i have "add" perms, then show the 'add new'
            // even if nothign was found, force TRUE, so i get my enter new
            return $found || $perms >= ACCESS_ADD;
		}


	function insertIntoRow(&$tab, $text)
		{
			if(!$text){
				return NULL;
			}
			$tab->addRow(array($text));				

			$colcount = $tab->getColCount();
			$rowcount = $tab->getRowCount() - 1; // zero based
// 			$tab->setCellContents($rowcount, 0, 
// 									  "ROW $rowcount COl $colcount");

			$tab->updateCellAttributes($rowcount,0,"colspan=$colcount");
	
		}

	function addSubTables(&$tab, $links)
		{
			$debug = 1;
			if(!$links){
				//print "NO LINKS!";
				return false;
			}
			$nearkey = $this->pk;	
			foreach($links as $backtable => $farkey){
				//TODO i have to check forbidden tables here too!
				$subview =& new CoopView(&$this->page, $backtable, 
										 $this->recurseLevel + 1,
										 &$this);
				$subview->obj->$farkey = $this->obj->$nearkey;
				if($debug){
					printf("linking %s.%s to %s.%s<br />", 
						   $this->table, $this->pk, 
						   $backtable, $farkey);
				}

				$recursed = $subview->recurseTable();
				//confessObj($subview, "addSubTables(): $backtable obj");
		
				if($recursed){
					$this->addSubTable(&$tab, sprintf('%s<br />%s', 
													  $backtable, 
													  $recursed));
				}
			}
		}
	
	
	//  checks if this table is repeated up the parent heirarchy
	// XXX bitrot! needs a-fixin'
	// also should move to coopobjet
	function isRepeatedTable($tablename)
		{
			$debug  = 1;
			if(!$this->parentCO){
				$debug && printf("%s is end of the line", $this->table);
				return 0;
			}
			if($this->parentCO->table == $tablename){
				$debug && printf("%s: %s is repeated", 
								 $this->table, $tablename);
				return 1;
			}
			$debug && printf("%s: %s  != %s, , checking up the line<br />", 
							 $this->table, $this->parentCO->table, 
							 $tablename);
			
			return $this->parentCO->isRepeatedTable($tablename);
		}


	/// generates an array of values, with permitted fields,
	/// and record buttons, ready for passing to html::table::addRow()
	function toArray($headerkeys = null)
		{

			$table = $this->obj->table();
			$row = $this->reorder($this->obj->toArray());
			foreach($row as $key => $val){

				// this is where the fun begins.

				if(!empty($headerkeys) && !in_array($key, $headerkeys)){
                    //skip those not in header. MUST BE IN SYNC WITH HEADER!
                    continue;
                }

                // XXX better way to do all this dispatching
				if($this->isPermittedField($key) < ACCESS_VIEW){
                    //for USERLEVEL. mask the data. but put placeholder
                    //so that it's in sync with header
                    $res[] = '';
                } else if(!empty($this->obj->fb_displayCallbacks) &&
                          in_array($key, 
                                   array_keys($this->obj->fb_displayCallbacks)))
                {
                    if(!is_callable(array($this->obj, 
                                          $this->obj->fb_displayCallbacks[$key])))
                    {
                        PEAR::raiseError("'{$this->obj->fb_displayCallbacks[$key]}' is set as display callback for $key, but IT IS NOT CALLABLE! typo?", 666);
                    }
                    
                    $res[] = call_user_func(
                        array($this->obj, $this->obj->fb_displayCallbacks[$key]),
                        &$this, $val, $key);
                    
                } else if($table[$key] & DB_DATAOBJECT_MYSQLTIMESTAMP){ 
                    $res[] = timestamp_db_php($val);
                } else if ($table[$key] &  DB_DATAOBJECT_TIME) {
                    $res[] = timestamp_db_php($val);
                } else if ($table[$key] &  DB_DATAOBJECT_DATE){
                    $res[] = sql_to_human_date($val);
                } else if(!empty($this->obj->fb_displayFormat) &&
                          in_array($key, 
                                   array_keys($this->obj->fb_displayFormat))) 
                {
                    $res[] = sprintf($this->obj->fb_displayFormat[$key], $val);
                } else if(!empty($this->obj->fb_URLFields) &&
                          in_array($key, $this->obj->fb_URLFields)) 
                {
                    $res[] = sprintf('<a href="%s">%s</a>',
                                     $this->page->fixURL($val), $val);
                } else if(!empty($this->obj->fb_currencyFields) &&
                          in_array($key, $this->obj->fb_currencyFields)) 
                {
                    //TODO: store thecurrency fmt in the conf file. yeah right.
                    $res[] = sprintf('$%0.02f', $val);
                } else if(!empty($this->obj->fb_textFields) &&
                          in_array($key, $this->obj->fb_textFields)) 
                {
                    $res[] = $this->fullText ? $val : 
                        sprintf("%.40s...",$val); // truncate, unless not
                } else if ($table[$key] &  DB_DATAOBJECT_BOOL){
                    //TODO: a little checkbox PNG would be nice
                    $res[] =  $val? 'X' :'';
                } else {
                    $res[] = nl2br(htmlspecialchars(
                                       $this->checkLinkField($key, $val)));
                }
				
			}

			//XXX hack! do this AFTER query, but not here.
			// BEFORE the query, backlink whatever will need to get real familyid
			
			$res[] = $this->recordButtons($row);
			
			// the Simple Version. useful for debuggin'
			//return array_values($this->obj->toArray());

			//$this->page->confessArray($res, "toArray() array");
			return $res;
		}

	function makeHeader()
		{
            $par = $this->getParent();
			///confessArray($this->obj->toArray(), 'makeheader:toarray');
			// get the fieldnames out the dataobject

            $labels = !empty($this->obj->fb_fieldLabels) ? 
                array_keys($this->obj->fb_fieldLabels) : array();

			foreach($this->reorder($this->obj->toArray()) as $key => $trash){
				//print "checking $key<br>";
                //force EVERYTHING for header. some might be theirs
                //also, it doesn't know year, so i have to force
				if($this->isPermittedField($key,true,true) &&
                    $key != $par->pk &&
                    in_array($key, $labels))
                {
                    $keys[] = $key;
					if($this->obj->fb_fieldLabels[$key]){
						$res[] = $this->obj->fb_fieldLabels[$key];
					} else {
						// XXX moot... if it ain't in fieldlabels, doesn't show
						$res[] = strtr(ucwords($key), '_', ' ');
					}
				}
			}
			
			$res[] = 'Actions';
			return array('titles' => $res,
                         'keys' => $keys);

		}

    // gives me an assoc array of the values, formatted all nicely like
    function toArrayWithKeys($headers = false)
        {
            if(!is_array($headers)){
                $headers =   $this->makeHeader();
            }
            $tmp = $this->toArray($headers['keys']);
            // fucking array_combine, dude
            foreach($headers['keys'] as $key){
                $audformatted[$key] = array_shift($tmp);
            }
            return $audformatted;
        }

	function tableTitle($contents)
		{
			//TODO: use DIV's instead of tables for this.
            $par = $this->getParent();
			$title = sprintf("%s %.50s (%d found)", 
							 $this->title(),
							 is_a($par, 'CoopObject') ? 
							 "for " . $par->getSummary() : "",
                             $this->obj->N);


            if($this->searchForm){
                $title .= ' ' . $this->searchForm->toHTML();
            } else {
                $title .=  ' School Year '. $this->getChosenSchoolYear(true);
            }
            

			$toptab = new HTML_Table(
				'class="tablecontainer"');
			$toptab->addRow(array($title, $this->actionButtons()), 
							'class="mysteryclass"', "TH");
			$toptab->addRow(array($contents), 'colspan="2"');
			
			return $toptab->toHTML();
		}

	function oneLineTable($find= 1)
		{
			// NOTE. this object's find, not the DBDO find
			if(!$this->find($find)){
				return;
			}

			$tab =& new HTML_Table();
		
			
			$tab->addRow(array($this->title(),
							   $this->actionButtons()), 
						 'class="tabletitles"', 'TH'); 

			while($this->obj->fetch()){
				//confessObj($this, 'onelinetable');
				$mainlink = $this->concatLinkFields();

				if($this->legacyCallbacks){
					$meat = $this->page->selfURL(array('value' => $mainlink, 
											 'inside' => $this->nastyInner(&$this->obj, 
															   'details'),
												 'base' => $this->legacyCallbacks['page']));
				} else {
					// handle the no-legacy-callbacks case
                    $meat = $this->page->selfURL(
                        array(
                            'value' => $mainlink,
                            'inside' => array( 
							'action' => 'details',
							'table' => $this->table,
							$this->prependTable($this->pk) => 
							$this->obj->{$this->pk}),
                            'base' => $this->obj->fb_usePage ? $this->obj->fb_usePage :
                        'generic.php')); 
				}

				$tab->addRow(array($meat,
								  $this->recordButtons(
									  $this->obj->toArray())),
							 'valign="top"');
			
			}
			$tab->altRowAttributes(1, 'class="altrow1"', 
								   'class="altrow2"');

			$res .= $tab->toHTML();
			return $res;

		}
	
	// i accept the row and i don't toarray it myself.
	// because i might need hidden fields that toarray would remove!
	// NOTE! row is deprciated, it's not used for dbdo-based stuff, only old
	function recordButtons(&$row, $par = true)
		{
            $res = '';

            // handle the simple case first: i have old callbacks
			if($this->legacyCallbacks){
				return recordButtons($row, $this->legacyCallbacks, 
 								 $this->legacyPerms, 
 								 $this->page->userStruct, 
								 "");
			}

            //checking here for a WHOLE ROW, familyid been inserted toarray
            $permitted = $this->isPermittedField();

			//confessObj($this, 'this');
			// the new style!
            $ra = !empty($this->obj->fb_recordActions) ? 
                $this->obj->fb_recordActions : $this->recordActions;

            foreach($ra as $action => $needlevel){
                //print "asking: $pair[1] $level,  i have: $permitted<br>";
                if($permitted >= $needlevel) {
                    $res .= $this->page->selfURL(
						array('value' => $this->actionnames[$action], 
						'inside' => array( 
							'action' => $action,
							'table' => $this->table,
							$this->prependTable($this->pk) => 
							$this->obj->{$this->pk}),
                              'base' =>!empty($this->obj->fb_usePage) ? 
                              $this->obj->fb_usePage :
                              'generic.php', 
                              'par' => $par)); 
                    $par || $res .= '&nbsp;';
                }
			}
			return $res;
		}




	function actionButtons($showview = 0)
		{
            $res = '';
			
			// handle the simple case first: i have old callbacks
			if($this->legacyCallbacks){
				return actionButtonsCore($this->page->auth, 
										 $this->legacyPerms, 
										 $this->page->userStruct, 
										 $this->page->userStruct['family_id'], 
										 $this->legacyCallbacks, 
										 $showview,  1);
			}


            //checking here for a WHOLE TABLE. 
            //i HACK HACK HACK and force it to my family,
            //because, at least SOME records (mine) i can do these actions to
            $permitted = $this->isPermittedField(null, true, true);

            $va = !empty($this->obj->fb_viewActions) ? 
                $this->obj->fb_viewActions : $this->viewActions;

            foreach($va as $action => $needlevel){
                //print "asking: $pair[1] $level,  i have: $permitted<br>";
                if($permitted < $needlevel) {
                    continue;
                }
                $in = array( 
                    'action' => $action,
                    'table' => $this->table);

                // XXX what exactly is this doing?? why not findTop?
                $par = $this->getParent(); // NOT  parentCO, _join!
                if(is_object($par) && is_a(&$par, 'CoopObject')){
                    //print "HEY!!! {$this->table} GOT ONE!!!";
                    $in[$this->prependTable($par->pk)] = 
                        $this->obj->{$par->pk};
                }

                if(!$this->isTop()){
                    $in['push'] = $this->table;
                }
                
                
                $res .= $this->page->selfURL(
                    array(
                        'value' =>$this->actionnames[$action], 
                        'inside' => $in,
                        'base' => empty($this->obj->fb_usePage) ? 
                        'generic.php' : $this->obj->fb_usePage));
                //                     confessObj($this->obj, 'this obj');
//                     confessObj($this->obj, 'parent obj');
                
			}
            return $res;
            
            //XXX why doesn't the below actually hide?
            //return "<div class=\"actionbuttons\">$res</div>";

		}


	// legacy recordbuttons crap. depreciated
	function nastyInner(&$obj, $action)
		{
			$res .= sprintf("action=%s", $action);

			if($obj->{$this->pk}){
				$res .= sprintf("&entry0[%s]=%d", 
								$this->pk, $obj->{$this->pk});
			}
			return $res;
		}
 


function schoolYearChooser()
{

    if($this->perms[NULL]['year'] < ACCESS_VIEW)
    {
        return;
    }



    if(!$this->isTop()){
        return;
    }

    
 
    $syform =& new HTML_QuickForm($this->table . '-search', false, false, 
                                  false, false, true);
    $el =& $syform->addElement('select', 'school_year', 'School Year', 
                               //TODO check ispermittedfield for allyears!
                               $this->getSchoolYears(null, true),
                               array('onchange' =>'javascript:submitForm()'));

    if($sid = thruAuthCore($this->page->auth)){
        $syform->addElement('hidden', 'coop', $sid); 
    }
    // this alllyears only makes sense if schoolyearchooser is ONLY
    // called when user has view permissions on not-this-year
    $syform->setDefaults(array('school_year' => 
                               $this->obj->fb_allYears ? '%' :
                               $this->page->currentSchoolYear));


    // XXX temporary hacks until i get my savevars shit working
    $syform->addElement('hidden', 'table', $this->table); 
    $syform->addElement('hidden', 'action', $this->page->vars['last']['action']); 
    $syform->addElement('hidden', $this->prependTable($this->pk), 
                        $this->page->vars['last']['id']); 
    $syform->addElement('hidden', 'realm', $this->page->vars['last']['realm']); 


    $this->searchForm =& $syform;
    

    // TODO: move getchosenschoolyear back here again, and do this in it!
    // and getelement(school_year) to get $el
    $foo = $el->getValue();
    $this->chosenSchoolYear = $foo[0];


    return;
}






    // MOVE THIS TO TABLE PERMISSIONS. as details, perhapsxs?
    function showPerms()
        {


            $res = "<h3>Detailed Permissions for {$this->obj->fb_formHeaderText}</h3>";
            $res .= $this->actionButtons();
            /// GETPERMS, AS CALCULATED
            $res .= sprintf("<pre>%s</pre>", print_r($this->perms, 1));


            //////  GETPERMS, from db
            // cute. the perms are about me, but they're executed in privs
            // so they execute with teh rights and permissions of privs.
            // TODO: do this gambit only if they have < 800 on this object
            $targ =& new CoopView(&$this->page, 'users', &$this);
            $this->debugWrap(5);
            $targ->obj->fb_formHeaderText = 
                "Total Permissions for {$this->obj->fb_formHeaderText}";
            $targ->obj->query(sprintf($this->permsQuery,
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->table));
            $res .= $targ->simpleTable(false);



            ///// USER ONLY
            $targ =& new CoopView(&$this->page, 'users', &$this);
            $targ->obj->fb_formHeaderText = "User Levels for ". 
                $this->page->userStruct['username'];
            $targ->obj->query(
                sprintf('select max(user_level) as user_level, 
max(group_level) as group_level,  max(year_level) as year_level, realm
from user_privileges 
left join realms on user_privileges.realm_id = realms.realm_id
where user_id = %d 
group by realm
order by realm',
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid']));
                    
            //confessObj($targ, 'targ');
            $res .= $targ->simpleTable(false);




            /// GROUP MEMBERSHIP
            $targ =& new CoopView(&$this->page, 'groups', &$this);
            $targ->obj->fb_formHeaderText = "Groups for ". 
                $this->page->userStruct['username'];

            $targ->obj->query(
                sprintf('
select name from groups 
left join users_groups_join using (group_id)
where users_groups_join.user_id = %d
',
                                      $this->page->auth['uid']));
                    
            //confessObj($targ, 'targ');
            $res .= $targ->simpleTable(false);



            ///// GROUPS, that user belongs to
            $targ =& new CoopView(&$this->page, 'groups', &$this);
            $targ->obj->fb_formHeaderText = "Group Levels for ". 
                $this->page->userStruct['username'];
            $targ->obj->query(
                sprintf('select name as Group_Name, 
max(user_level) as user_level, 
max(group_level) as group_level, 
max(year_level) as year_level, 
 realm
from user_privileges 
left join realms on user_privileges.realm_id = realms.realm_id
left join groups on groups.group_id = user_privileges.group_id
where (user_privileges.group_id in 
(select group_id from users_groups_join 
where user_id = %d)) 
group by realm
order by realm',
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid']));
                    
            //confessObj($targ, 'targ');
            $res .= $targ->simpleTable(false);



            
            /// TABLE
            $targ =& new CoopView(&$this->page, 'table_permissions', &$this);
                 
            $targ->obj->fb_formHeaderText = "Table Permissions for {$this->obj->fb_formHeaderText}";
            $targ->obj->query(
                sprintf('
select field_name, table_name, 
user_level, group_level, table_permissions.realm_id 
from table_permissions  
left join realms using (realm_id)
where table_name = "%s"
order by table_name,field_name;
',
                                      $this->table));
                    
            confessObj($targ, 'targ');
            $res .= $targ->simpleTable(false);


           /// REEPORTS
            $targ =& new CoopView(&$this->page, 'report_permissions', &$this);
                 
            $targ->obj->fb_formHeaderText = "Report Permissions";
            $targ->obj->query(
                sprintf('
select report_name, page, 
user_level, group_level, report_permissions.realm_id 
from report_permissions  
left join realms using (realm_id)
order by report_name'));
                    
            confessObj($targ, 'targ');
            $res .= $targ->simpleTable(false);


            return $res;
        }

} // END COOP VIEW CLASS


////KEEP EVERTHANG BELOW

?>