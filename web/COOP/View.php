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


    //chain up
	function CoopView (&$page, $table, &$parentCO, $level = 0)
		{
			parent::CoopObject(&$page, $table, &$parentCO, $level);
            $this->obj->CoopView =& $this;  //used by funcs in dbdo

            $this->joinTo('family_id');
            $this->joinTo('school_year');
            $page->confessArray($this->obj->joinPaths,
                                      'coopView() joinpaths found', 3);
            
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

			// NOTE. this object's find, not the DBDO find
			if(!$this->find($find)){
				return;
			}

			$tab = new HTML_Table('  bgcolor="#ffffff"');	
		
			while($this->obj->fetch()){
				if(!$rowcnt++){
					// MUST do this *after* fetch, to handl custom queries
					// where column names are different from what's in obj
                    $header = $this->makeHeader();
                    $this->page->confessArray($header, 'header', 2);
					$tab->addRow(array_values($header['titles']), 
							 'bgcolor=#aabbff align=left', 'TH'); 
				}
				//$tab->addRow(array_values($this->obj->toArray()));
				$tab->addRow($this->toArray($header['keys']),'valign="top"');
			
			}
			
			$tab->altRowAttributes(1, 'bgcolor="#dddddd"', 
								   'bgcolor="#ccccff"');

			
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
                             'bgcolor="#cccccc"' );
			
			}
			if($this->extraRecordButtons){
				$tab->addRow(array("", $this->extraRecordButtons));
			}

			return $this->tableTitle($tab->toHTML());
	
		}
	
	function find($find)
		{
            // i stuck this into find so i don't have to duplicate it
            // in horiztable, simpletable, onelinetable, etc
            // and i have to FORCE ispermitted to return the userlevel
            $perms = $this->isPermittedField(NULL,true);
            if($perms < ACCESS_VIEW){
                $this->page->printDebug(
                    "CoopView::find($this->table) NO VIEW PERMS!", 2 );
                return false;
            }
			if($find){
				$found = $this->obj->find();
			} else {
				$found = $this->obj->N;
			}
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
					printf("linking %s.%s to %s.%s<br>", 
						   $this->table, $this->pk, 
						   $backtable, $farkey);
				}

				$recursed = $subview->recurseTable();
				//confessObj($subview, "addSubTables(): $backtable obj");
		
				if($recursed){
					$this->addSubTable(&$tab, sprintf('%s<br>%s', 
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
			$debug && printf("%s: %s  != %s, , checking up the line<br>", 
							 $this->table, $this->parentCO->table, 
							 $tablename);
			
			return $this->parentCO->isRepeatedTable($tablename);
		}


	/// generates an array of values, with permitted fields,
	/// and record buttons, ready for passing to html::table::addRow()
	function toArray($headerkeys = null)
		{

			$table = $this->obj->table();
			$row = $this->obj->toArray();
			foreach($row as $key => $val){

				// this is where the fun begins.

				if(is_array($headerkeys) && !in_array($key, $headerkeys)){
                    //skip those not in header. MUST BE IN SYNC WITH HEADER!
                    continue;
                }

                // XXX better way to do all this dispatching
				if($this->isPermittedField($key) < ACCESS_VIEW){
                    //for USERLEVEL. mask the data. but put placeholder
                    //so that it's in sync with header
                    $res[] = '';
                } else if($table[$key] & DB_DATAOBJECT_MYSQLTIMESTAMP){ 
                    $res[] = timestamp_db_php($val);
                } else if ($table[$key] &  DB_DATAOBJECT_TIME) {
                    $res[] = timestamp_db_php($val);
                } else if ($table[$key] &  DB_DATAOBJECT_DATE){
                    $res[] = sql_to_human_date($val);
                } else if(is_array($this->obj->fb_displayFormat) &&
                          in_array($key, $this->obj->fb_displayFormat)) 
                {
                    $res[] = sprintf($this->obj->fb_displayFormat, $val);
                } else if(is_array($this->obj->fb_URLFields) &&
                          in_array($key, $this->obj->fb_URLFields)) 
                {
                    $res[] = sprintf('<a href="%s">%s</a>',
                                     $this->page->fixURL($val), $val);
                } else if(is_array($this->obj->fb_textFields) &&
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
			// the right thing to do is to fish familyid out of backlinks!
			if(!in_array('family_id', 
						array_keys(get_object_vars($this->obj))))
			{
				$row['family_id'] = 0;
			}
			
			$res[] = $this->recordButtons($row);
			
			// the Simple Version. useful for debuggin'
			//return array_values($this->obj->toArray());

			//$this->page->confessArray($res, "toArray() array");
			return $res;
		}

	function makeHeader()
		{

			///confessArray($this->obj->toArray(), 'makeheader:toarray');
			// get the fieldnames out the dataobject
			foreach($this->obj->toArray() as $key => $trash){
				//print "checking $key<br>";
				if($this->isPermittedField($key, true)){
                    $keys[] = $key;
					if($this->obj->fb_fieldLabels[$key]){
						$res[] = $this->obj->fb_fieldLabels[$key];
					} else {
						// TODO: uppercase this thing, replace _ with spaces
						$res[] = $key;
					}
				}
			}
			
			$res[] = 'Actions';
			return array('titles' => $res,
                         'keys' => $keys);

		}


	function tableTitle($contents)
		{
			//TODO: use DIV's instead of tables for this.
			$title = sprintf("%s %.50s", 
							 $this->title(),
							 $this->parentCO ? 
							 "for " . $this->parentCO->getSummary() : "");
		
			$toptab = new HTML_Table(
				'bgcolor="#aa99ff" cellpadding="0" cellspacing="0"');
			$toptab->addRow(array($title, $this->actionButtons()), 
							'align="center"', "TH");
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
						 'bgcolor="#aabbff" align="left"', 'TH'); 

			while($this->obj->fetch()){
				//confessObj($this, 'onelinetable');
				$mainlink = $this->concatLinkFields(&$this->obj);

				if($this->legacyCallbacks){
					$meat = $this->page->selfURL($mainlink, 
											 $this->nastyInner(&$this->obj, 
															   'details'),
												 $this->legacyCallbacks['page']);
				} else {
					// handle the no-legacy-callbacks case
                    $meat = $this->page->selfURL(
						$mainlink,
						array( 
							'action' => 'details',
							'table' => $this->table,
							$this->prependTable($this->pk) => 
							$this->obj->{$this->pk}),
                        $this->obj->fb_usePage ? $this->obj->fb_usePage :
                        'generic.php'); 
				}

				$tab->addRow(array($meat,
								  $this->recordButtons(
									  $this->obj->toArray())),
							 'valign="top"');
			
			}
			$tab->altRowAttributes(1, 'bgcolor="#dddddd"', 
								   'bgcolor="#ccccff"');

			$res .= $tab->toHTML();
			return $res;

		}
	
	// i accept the row and i don't toarray it myself.
	// because i might need hidden fields that toarray would remove!
	function recordButtons(&$row)
		{

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
            $ra = is_array($this->obj->fb_recordActions) ? 
                $this->obj->fb_recordActions : $this->recordActions;

            foreach($ra as $action => $needlevel){
                //print "asking: $pair[1] $level,  i have: $permitted<br>";
                if($permitted >= $needlevel) {
                    $res .= $this->page->selfURL(
						$this->actionnames[$action], 
						array( 
							'action' => $action,
							'table' => $this->table,
							$this->prependTable($this->pk) => 
							$this->obj->{$this->pk}),
                        $this->obj->fb_usePage ? $this->obj->fb_usePage :
                        'generic.php'); 
                }
			}
			return $res;
		}




	function actionButtons($showview = 0)
		{
			
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
            $this->obj->family_id = $this->page->userStruct['family_id'];
            $permitted = $this->isPermittedField();
            $va = is_array($this->obj->fb_viewActions) ? 
                $this->obj->fb_viewActions : $this->viewActions;
            foreach($va as $action => $needlevel){
                //print "asking: $pair[1] $level,  i have: $permitted<br>";
                if($permitted >= $needlevel) {
                    $res .= $this->page->selfURL(
						$this->actionnames[$action], 
						array( 
							'action' => $action,
							'table' => $this->table),
                        $this->obj->fb_usePage ? $this->obj->fb_usePage :
                        'generic.php'); 
                }
			}
            return $res;

		}

	// note, this is not very well-used, but it will be.  i'll replace
	// legacy recordbuttons with stuff using this
	function nastyInner(&$obj, $action)
		{
			$res .= sprintf("action=%s", $action);

			if($obj->{$this->pk}){
				$res .= sprintf("&entry0[%s]=%d", 
								$this->pk, $obj->{$this->pk});
			}
			return $res;
		}


    // XXX UNUSED CRUFT! 
	function checkMenuLevel()
		{
			return checkMenuLevel($this->page->auth, 
								  $this->page->userStruct, 
								  $this->legacyCallbacks, 
								  $this->legacyCallbacks['fields']);
		
		}



} // END COOP VIEW CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP VIEW -->


