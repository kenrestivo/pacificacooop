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
require_once 'HTML/QuickForm.php';
require_once('DB/DataObject/FormBuilder.php');
require_once('Pager/Pager.php');
require_once('object-config.php');

//////////////////////////////////////////
/////////////////////// COOP VIEW CLASS
class coopView extends CoopObject
{
	var $backlinks;				// list of links that are linked FROM here
	var $forwardLinks;
	

	var $legacyCallbacks;			// hack for old callbacks
	var $legacyPerms; 			// cache of permissions for this page ($p)
	//var $legacyFields;  //YAGNI. i hope

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

	function getLinks()
		{
			
//			confessObj($this, "view");
			global $_DB_DATAOBJECT;
			//confessObj($_DB_DATAOBJECT, "getLinks() dataobject");
			$tab =  $this->obj->tableName(); // XXX dup with $this->table
			$links =& $_DB_DATAOBJECT['LINKS'][$this->obj->database()];
			$this->forwardLinks = $links[$tab];
			$this->page->confessArray($links, 
									  "getLinks: links for $this->table");
			foreach($links as $maintable => $mainlinks){
				foreach ($mainlinks as $nearcol => $farline){
					// split up farline and chzech it
					list($fartable, $farcol) = explode(':', $farline);
					if($fartable == $tab){
						$res[$maintable] = $nearcol;
					}
				}
			}
			$this->backlinks = $res;
			$this->page->confessArray($res,
									  "getLinks() backlinks for $this->table");
			return $this->backlinks;
		}
	
	// formats object is current in this object, um, as a table
	function simpletable($find= 1)
		{
			if($find){
				$found = $this->obj->find();
				
				if($found < 1){
					return false;
				}
			}
			$tab = new HTML_Table('  bgcolor="#ffffff"');	
		
			$tab->addRow($this->makeHeader(), 
						 'bgcolor=#aabbff align=left', 'TH'); 
			
			while($this->obj->fetch()){
				//$tab->addRow(array_values($this->obj->toArray()));
				$tab->addRow($this->toArray());
			
			}
			
			$tab->altRowAttributes(1, 'bgcolor="#dddddd"', 
								   'bgcolor="#ccccff"');

			
			return $this->tableTitle($tab->toHTML());

		}

	function horizTable($find = 1)
		{
			if($find){
				$found = $this->obj->find();
				
				if($found < 1){
					return false;
				}
			}
						$tab =& new HTML_Table();
			$tab->addCol($this->makeHeader(), 
						 'align=right', 'TH');

			while($this->obj->fetch()){
				$tab->addCol($this->toArray(),'bgcolor=#cccccc' );
			
			}

			
			return $this->tableTitle($tab->toHTML());
	
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

	function isPermittedField($key)
		{
			// if it's a key, and we don't show them, then no
			if($key == $this->pk && !$this->obj->fb_hidePrimaryKey){
				return 0;
			}
			//we don't show if not in fieldstorender
			if($this->obj->fb_fieldsToRender && 
			   !in_array($key, $this->obj->fb_fieldsToRender)){
				return 0;
			}

			return 1;
		}


	function toArray()
		{
			
			$row = $this->obj->toArray();
			foreach($row as $key => $val){
				// this is where the fun begins.
				if($this->isPermittedField($key)){
					$res[] = $this->checkLinkField(&$this->obj, $key, $val);
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
			// get the fieldnames out the dataobject
			foreach($this->obj->toArray() as $key => $trash){
				if($this->isPermittedField($key)){
					if($this->obj->fb_fieldLabels[$key]){
						$res[] = $this->obj->fb_fieldLabels[$key];
					} else {
						$res[] = $key;
					}
				}
			}
			
			$res[] = 'Actions';
			return $res;

		}

	function title()
		{
			if($this->obj->kr_longTitle){ 
				return $this->obj->kr_longTitle;
			}
			return ucwords($this->table);
		}

	function tableTitle($contents)
		{
			$title = sprintf("%s %.50s", 
							 $this->title(),
							 $this->parentCO ? 
							 "for " . $this->parentCO->getSummary() : "");
		
			$toptab = new HTML_Table('bgcolor="#aa99ff" cellpadding="0" cellspacing="0"');
			$toptab->addRow(array($title, $this->actionButtons()), 'align="center"', "TH");
			$toptab->addRow(array($contents), 'colspan="2"');
			
			return $toptab->toHTML();
		}

	function oneLineTable($find= 1)
		{
			if($find){
				$found = $this->obj->find();
				
				if($found < 1){
					return false;
				}
			}
			$tab =& new HTML_Table();
		
			
			$tab->addRow(array($this->title(),
							   $this->actionButtons()), 
						 'bgcolor="#aabbff" align="left"', 'TH'); 

			while($this->obj->fetch()){
				//confessObj($this, 'onelinetable');
				$mainlink = $this->concatLinkFields(&$this->obj);

				$meat = $this->page->selfURL($mainlink, 
											 $this->nastyInner(&$this->obj, 
															   'details'),
											 $this->legacyCallbacks['page']);

				$tab->addRow(array($meat,
								  $this->recordButtons(
									  $this->obj->toArray())));
			
			}
			$tab->altRowAttributes(1, 'bgcolor="#dddddd"', 
								   'bgcolor="#ccccff"');

			$res .= $tab->toHTML();
			return $res;

		}
	
	function recordButtons(&$row)
		{
 		 	return recordButtons($row, $this->legacyCallbacks, 
 								 $this->legacyPerms, 
 								 $this->page->userStruct, 
								 "");
			
		}




	function actionButtons($showview = 0)
		{
			
// XXX doesn't work because i don't have fields struct in here. yet. but i will.			
// 		if($showview){
// 				$admin = $this->legacyPerms['group_level'] >= ACCESS_ADD ? 1 : 0;
// 				$showview = $this->legacyCallbacks['count']($u['family_id'], $callbacks, $fields) + $admin;
// 			}
			return actionButtonsCore($this->page->auth, 
									 $this->legacyPerms, 
									 $this->page->userStruct, 
									 $this->page->userStruct['family_id'], 
									 $this->legacyCallbacks, 
									 $showview,  1);
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


