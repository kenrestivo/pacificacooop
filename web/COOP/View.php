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
	var $parentSummary; 		// miserable hack. summary of top parent object
	var $legacyPage;			// hack to let it know what old page 2 use

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
			$tab =& new HTML_Table();
		
			$this->addHeader(&$tab);

			while($this->obj->fetch()){
				//$tab->addRow(array_values($this->obj->toArray()));
				$tab->addRow($this->toArray());
			
			}
			$tab->altRowAttributes(1, "bgcolor=#CCCCC", "bgcolor=white");
			$res .= $this->tableTitle($summary);
			$res .= $tab->toHTML();
			return $res;

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
	function isRepeatedTable($tablename)
		{
			$debug  = 1;
			if(!$this->parentObj){
				$debug && printf("%s is end of the line", $this->table);
				return 0;
			}
			if($this->parentObj->table == $tablename){
				$debug && printf("%s: %s is repeated", 
								 $this->table, $tablename);
				return 1;
			}
			$debug && printf("%s: %s  != %s, , checking up the line<br>", 
							 $this->table, $this->parentObj->table, 
							 $tablename);
			
			return $this->parentObj->isRepeatedTable($tablename);
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
			
			foreach($this->obj->toArray() as $key => $val){
				// this is where the fun begins.
				if($this->isPermittedField($key)){
					$res[] = $this->checkLinkField(&$this->obj, $key, $val);
				}
			}

			// the Simple Version. useful for debuggin'
			//return array_values($this->obj->toArray());

			//$this->page->confessArray($res, "toArray() array");
			return $res;
		}

	function addHeader(&$tab)
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
			
			$tab->addRow($res, 'bgcolor=#9999cc', 'TH'); 
		}

	function title()
		{
			if($this->obj->kr_longTitle){ 
				return $this->obj->kr_longTitle;
			}
			return ucwords($this->table);
		}

	function tableTitle()
		{
			$res = sprintf("<hr><h2>%s %.50s</h2>", 
						   $this->title(),
						   $this->parentSummary ? 
						   "for " . $this->parentSummary : "");
														
			return $res;
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
		
			
			$tab->addRow(array($this->title()), 'bgcolor=#9999cc', 'TH'); 

			while($this->obj->fetch()){
				//confessObj($this, 'onelinetable');
				$mainlink = $this->concatLinkFields(&$this->obj);

				$meat = $this->page->selfURL($mainlink, 
											 $this->nastyInner(&$this->obj, 
															   'details'),
											 $this->legacyPage);

				$tab->addRow(array($meat));
			
			}
			//	$tab->altRowAttributes(1, "bgcolor=#CCCCC", "bgcolor=white");
			$res .= $tab->toHTML();
			return $res;

		}
	
	function nastyInner(&$obj, $action)
		{
			return sprintf("entry0[%s]=%d&action=%s", 
						   $this->pk, $obj->{$this->pk},
						   $action);
			
		}

} // END COOP VIEW CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP VIEW -->


