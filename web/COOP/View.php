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

           // reset debuglevel in obj, which may bave been set by save!
            $this->debugWrap(5);

 //            $this->joinTo('family_id');
//             $this->joinTo('school_year');
 //            $page->confessArray(&$this->obj->fb_joinPaths,
//                                       'coopView() joinpaths found', 3);
             // XXX doesn't work yet $this->doJoins();
            
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
            
            //vital for tables with BLOBS in 'em
            $this->dietBlobs($find);


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

			
			return $this->tableTitle($rowcnt ? $tab->toHTML(): 'Nothing found.');

		}

	function horizTable($find = true)
		{
			// NOTE. this object's find, not the DBDO find
			if(!$this->find($find)){
				return;
			}


			$tab =& new HTML_Table();
            $header = $this->makeHeader();
			$tab->addCol($header['titles'], array('align' => 'right',
                                                  'class' => 'tabletitles'), 
                         'TH');

            /// XXX fetch will return false if get() used, so i hack
            if(!$find){ 
                $tab->addCol($this->toArray($header['keys']), 
                             'class="altrow1"');
            }

			while($this->obj->fetch()){
                $tab->addCol($this->toArray($header['keys']), 
                             'class="altrow1"');
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


            $this->page->printDebug("CoopView::find({$this->table})", 1);

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


    /// this is critical. i am hacking around and NOT fetching blobs
    /// to save time on large db queries. these can get crazy if i
    /// grab the entire blob, just to show a few character summary of it
    /// so instead, i'm doing a naughty here and NOT fetching the blob,
    /// instead only getting the cache
    function dietBlobs($find)
        {
            // skip if i am manually querying,
            // or if no way to determine textfields
            if(!$find || empty($this->obj->fb_textFields)){
                return; 
            }

            $this->obj->selectAdd();// clear!
            foreach($this->obj->fb_fieldLabels as $key => $val){
                if(in_array($key, $this->obj->fb_textFields))
                {
                    $this->obj->selectAdd(
                        sprintf('_cache_%s', $key));
                    $this->obj->selectAdd("'ERROR! use _cache_$key field instead' as $key");
                } else {
                    $this->obj->selectAdd($this->table . '.' . $key);
                }
            }

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
    /// TODO: i really need to htmlentites all this stuff.
	function toArray($headerkeys = null)
		{
            
            $this->recoverSafePK();



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
                                     $this->page->fixURL(htmlentities($val)), 
                                     htmlentities($val));
                } else if(!empty($this->obj->fb_currencyFields) &&
                          in_array($key, $this->obj->fb_currencyFields)) 
                {
                    //TODO: store thecurrency fmt in the conf file. yeah right.
                    $res[] = sprintf('$%0.02f', $val);
                } else if(!empty($this->obj->fb_textFields) &&
                          in_array($key, $this->obj->fb_textFields)) 
                {
                    //NOTE nl2br is to deal with old text imports
                    //TODO: don't use val, specifically go get _cache_$key here
                    $res[] = nl2br($this->fullText ? '<div>' . $val . '</div>' : 
                                   strip_tags(
                                       sprintf('%.' . 
                                               COOP_MAX_LONGTEXT_DISPLAY .
                                               's...',
                                               $this->obj->{'_cache_' . $key}))); 
                } else if ($table[$key] &  DB_DATAOBJECT_BOOL){
                    //TODO: a little checkbox PNG would be nice
                    $res[] =  $val? 'X' :'';
                } else {
                    $res[] = nl2br(htmlentities(
                                       $this->checkLinkField($key, $val)));
                }
				
			}

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
				//print "checking $key<br />";
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
            // array_combine() is only in PHP5. bastards.
            foreach($headers['keys'] as $key){
                $audformatted[$key] = array_shift($tmp);
            }
            return $audformatted;
        }

	function tableTitle($contents)
		{
			//TODO: use DIV's instead of tables for this.
            $par = $this->getParent();
			$title = htmlentities(sprintf("%s %.50s (%d found)", 
							 $this->title(),
							 is_a($par, 'CoopObject') ? 
							 "for " . $par->concatLinkFields() : "",
                             $this->obj->N));


            if($this->searchForm){
                $title .= ' ' . $this->searchForm->toHTML();
            } else {
                $title .=  ' School Year '. $this->getChosenSchoolYear(true);
            }
            

			$toptab = new HTML_Table(
				'class="tablecontainer"');
			$toptab->addRow(array($title, 
                                  $this->actionButtons()), 
							'class="tablecontainer"', "TH");
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
                $this->recoverSafePK();

				//confessObj($this, 'onelinetable');
				$mainlink = htmlentities($this->concatLinkFields());

				if($this->legacyCallbacks){
					$meat = $this->page->selfURL(
                        array('value' => $mainlink, 
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
			// NOT EMPTY()! i want empty arrays to override!
            $ra = is_array($this->obj->fb_recordActions) ? 
                $this->obj->fb_recordActions : $this->recordActions;


            foreach($ra as $action => $needlevel){
                //print "asking: $pair[1] $level,  i have: $permitted<br />";
                if($permitted >= $needlevel) {
                    $res .= $this->page->selfURL(
						array(
                            'value' => $this->actionnames[$action],
                            'inside' => array( 
                                'action' => $action,
                                'table' => $this->table,
                                'push' => $this->table,
                                $this->prependTable($this->pk) => 
                                $this->obj->{$this->pk}),
                            'base' =>!empty($this->obj->fb_usePage) ? 
                            $this->obj->fb_usePage :
                            'generic.php', 
                            'par' => $par)); 
                    $par || $res .= '&nbsp;';
                }
			}
            return '<div class="actions">' . $res . '</div>';
		}



    // TODO: showview is a legacy. when i whack legacy, remove showview
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
                //print "asking: $pair[1] $level,  i have: $permitted<br />";
                if($permitted < $needlevel) {
                    continue;
                }
                
                // skip current action
                if($action == $this->page->vars['last']['action'] &&
                    $this->table == $this->page->vars['last']['table'])
                {
                    continue;
                }
                
                $in = array( 
                    'action' => $action,
                    'table' => $this->table);

                // if i'm a sub-table under some other table
                // as in the case of a details view
                // then be sure to include the ID and table/fieldname of it!
                $par = $this->getParent(); // NOT  parentCO, _join!
                if(is_object($par) && is_a(&$par, 'CoopObject')){
                    //print "HEY!!! {$this->table} GOT ONE!!!";
                    $in[$this->prependTable($par->pk)] = 
                        $this->obj->{$par->pk};
                }
                
                // always pushing from a view
                $in['push'] = $this->table;
                                
                $res .= $this->page->selfURL(
                    array(
                        'value' =>$this->actionnames[$action], 
                        'inside' => $in,
                        'base' => empty($this->obj->fb_usePage) ? 
                        'generic.php' : $this->obj->fb_usePage));
                //                     confessObj($this->obj, 'this obj');
//                     confessObj($this->obj, 'parent obj');
                
			}
            return '<div class="actions">' . $res . '</div>';
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
            
            if(!empty($this->searchForm)){
                //there's already one there
                return;
            }
            
            
            $syform =& new HTML_QuickForm($this->table . '-schoolyearchooser',
                                          false, false, false, false, true);
            $syform->removeAttribute('name');
            $syform->removeAttribute('target');
            $el =& $syform->addElement('select', 'school_year', 'School Year', 
                                       //TODO check ispermittedfield for allyears!
                                       $this->getSchoolYears(null, true),
                                       array('onchange' =>'this.form.submit()'));
            
            if($sid = thruAuthCore($this->page->auth)){
                $syform->addElement('hidden', 'coop', $sid); 
            }
            
            if(!empty($this->page->vars['last']['chosenSchoolYear'])){
                $defaultsy = $this->page->vars['last']['chosenSchoolYear'];
            } else if (!empty($this->obj->fb_allYears)) {
                // this alllyears only makes sense if schoolyearchooser is ONLY
                // called when user has view permissions on not-this-year
                $defaultsy = '%';
            } else {
                $defaultsy = $this->page->currentSchoolYear;
            }
            
            $syform->setDefaults(array('school_year' => $defaultsy));
            
            
            $this->searchForm =& $syform;
            
            
            // TODO: move getchosenschoolyear back here, and do this in it!
            // and getelement(school_year) to get $el
            $foo = $el->getValue();
            $this->chosenSchoolYear = $foo[0];
            $this->page->vars['last']['chosenSchoolYear'] = $this->chosenSchoolYear;
            
            return;
        }


    function showLinkDetails()
        {
            // bah. pk might not be an int. it's a string in session_info!
            // php coerces a string to 0 for integer comparisons. bah.n
            if(empty($this->obj->{$this->pk}) || 
               (is_numeric($this->obj->{$this->pk}) && 
                $this->obj->{$this->pk} == 0))
            {
                PEAR::raiseError(
                    sprintf('CoopView::showLinkDetails(%s): having heartburn over index [%s], cant show details', 
                            $this->table, $this->obj->{$this->pk}), 
                    666);
            }

            $res = "";
            // try to intelligently find all forward/backlinks
            // or intermediately, adapt findfamily, and pass a list of tables
            // let the code go fish out the path to 'em

            foreach($this->allLinks() as $table => $ids){
                list($nearid, $farid) = $ids;
                $this->page->printDebug("$this->table  link for $nearid {$this->obj->$nearid}  $table", 4);
                $aud =& new CoopView(&$this->page, $table, &$this);
                $tabs = $aud->obj->table();
                $farwhole = $farid;
                if(!empty($tabs[$farid])){
                    $farwhole = "{$aud->table}.$farid";
                }
                $aud->obj->whereAdd(sprintf('%s = %d', 
                                            $farwhole, $this->obj->{$nearid}));
                //confessObj($aud, 'aud');
                $aud->debugWrap(5);
                $res .= $aud->simpleTable();
            }

            // now, extradetails is a bit of a hack. it is used for join links
            // there has to be a better way of doing it, generically
            if(is_array($this->obj->fb_extraDetails)){
                foreach($this->obj->fb_extraDetails as $path){
                    // XXX this only handles one-degree-of-separation!
                    list($join, $dest) = explode(':', $path);
                    $co2 =& new CoopObject(&$this->page, $join, &$this);
                    $co2->obj->whereAdd(sprintf('%s.%s = %d', 
                                                $co2->table,
                                                $this->pk, 
                                                $this->obj->{$this->pk}));
                    $real =& new CoopView(&$this->page, $dest, &$co2);
                    $real->obj->orderBy('school_year desc');
                    $real->protectedJoin($co2);
 
                    /// XXX this is sketchy. could cause GRIEF
                    /// i need to add the $co2's PK to the REAL object
                    /// because the actionbuttons will need it!
//                     $real->obj->selectAdd(sprintf('%s.%s', 
//                                              $co2->table, 
//                                              $co2->pk));

                    $res .= $real->simpleTable();
                }
            }

            return $res;
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

// TODO: format this prettier-like, with floats, etc
function getAlert()
        {
            $res = '';
            if(is_callable(array($this->obj, 'fb_display_alert'))){
                $alertbody =  $this->obj->fb_display_alert(&$this);
                if($alertbody){
                    $res .= sprintf('<p><img src="/images/Achtung-small.png" alt="Achtung!" />&nbsp;%s</p>', 
                                   $alertbody);
                }
            }
            return $res;
        }
  

    function getSummary()
        {
            if(is_callable(array($this->obj, 'fb_display_summary'))){
                $this->page->printDebug('calling callback for summary', 2);
                return  '<div>' . $this->obj->fb_display_summary(&$this) . '</div>';
            }
        }


    // this does nothing, because it appears to be impossible to do what i want
    function titleJSHack()
        {
            $title = sprintf('%s - %s', 
                             $this->fb_formHeaderText,
                             $this->actionnames[$this->page->vars['last']['action']]);
            
            return wrapJS('', 
                          'COOP_TITLE_HACK');
        }


    function alphaPager()
        {
            // use latest of course, if available
            if(!empty($_REQUEST['startletter'])){
                $this->page->vars['last']['startletter'] = $_REQUEST['startletter'];
            }
            // defautl to A if not there
            $sl = empty($this->page->vars['last']['startletter']) ? 'A': 
    $this->page->vars['last']['startletter'];

            $res .= '<div>Choose a letter to view:</div>';
            foreach(range('A', 'Z') as $ltr){
                $letterlist[] = $sl == $ltr ? $sl : $this->page->selfURL(
                    array('value' => $ltr,
                          'par' => '',
                          'inside' => array('startletter' => $ltr)
                        ));
            }
            $res .= implode('&nbsp;', $letterlist);
            
            
            // ok, find it!
            $this->obj->whereAdd("last_name like '$sl%'");

            return $res;
        }



} // END COOP VIEW CLASS


////KEEP EVERTHANG BELOW


?>