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


	// returns an action structure: tables[tablename][action], etc
	function getBackLinks()
		{
			
//			confessObj($this, "view");
			global $_DB_DATAOBJECT;
			//confessObj($_DB_DATAOBJECT, "getBackLinks() dataobject");
			$tab =  $this->obj->tableName(); // XXX dup with $this->table
			$links =& $_DB_DATAOBJECT['LINKS'][$this->obj->database()];
			$this->forwardLinks = $links[$tab];
			$this->page->confessArray($links, 
									  "getBackLinks: links for $this->table");
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
									  "getBackLinks() backlinks for $this->table");
			return $this->backlinks;
		}
	
	// formats object is current in this object, um, as a table
	function simpleTable()
		{
			$this->obj->find();
			$this->getPK(); // must this be after find? rather constructor.
			//TODO return null or something, if nothing found
			$tab =& new HTML_Table();
			while($this->obj->fetch()){
				//$tab->addRow(array_values($this->obj->toArray()));
				$tab->addRow($this->toArray());
			
			}
			return $tab->toHTML();
		}

	function addSubTable(&$tab, $text)
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

	
	function addSubTables(&$tab)
		{
			if(!$this->backlinks){
				return false;
			}
			$nearkey = $this->pk;	
			foreach($this->backlinks as $backtable => $farkey){
				//TODO i have to check forbidden tables here too!
				$subview =& new CoopView(&$this->page, $backtable, 
										 $this->recurseLevel + 1,
										 &$this);
				$subview->obj->$farkey = $this->obj->$nearkey;
// 				printf("linking %s.%s to %s.%s<br>", 
// 					   $this->table, $this->pk, 
// 					   $backtable, $farkey);
				//confessObj($subview, "addSubTables(): $backtable obj");
				$recursed = $subview->recurseTable();
		
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
			if(!$this->parentObj){
				return 0;
			}
			if($this->parentObj->table == $tablename){
			
				return 1;
			}
//			print "$this->parentObj->table != $tablename<br>";
			return $this->parentObj->isRepeatedTable($tablename);
		}

	function addForwardTables(&$tab)
		{
	
			//confessObj(&$this, "addForwardTables() ");	
			$this->page->confessArray($this->links, 
									  "addForwardTables() links");		
			if(!$this->forwardLinks){
				return false;
			}
			

			foreach($this->forwardLinks as $nearkey => $farline){
				list($fartable, $farcol) = explode(':', $farline);
				if(!$this->isRepeatedTable($fartable)){
					$this->addSubTable(&$tab, "FORWARDtable $fartable <br>");		
				}
			}


		}


	function toArray()
		{
			
			foreach($this->obj->toArray() as $key => $val){
				// this is where the fun begins.
				$res[] = $this->checkLinkField(&$this->obj, $key, $val);

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
				if($this->obj->fb_fieldLabels[$key]){
					$res[] = $this->obj->fb_fieldLabels[$key];
				} else {
					$res[] = $key;
				}
			}
			
			$tab->addRow($res, 'bgcolor=#9999cc', 'TH'); 
		}


	function recurseTable()
		{
			//confessObj($this->obj, "recurseTable(): obj $this->table");
			$found = $this->obj->find();

			if($found < 1){
				return false;
			}

			//print "found $found for $this->table<br>";

			$this->getBackLinks();	// MUST be after find!

			$jointable = 0; //preg_match('/_join/', $this->table);

			// only indent the sub-level tables
			$attr = $this->recurseLevel ? 'class=sub' : NULL;
			$tab =& new HTML_Table($attr);

			// TODO:  forbidden names XXX nasty hack
			if(!$jointable){
				//$this->addTableTitle(&$tab);
				$this->addHeader(&$tab);
			}
			while($this->obj->fetch()){
				// the main row.
				if(!$jointable){
					$tab->addRow($this->toArray());
				}
				//subrows
				$this->addSubTables(&$tab);
				$this->addForwardTables(&$tab);

			}

			$tab->altRowAttributes(1, "bgcolor=#CCCCC", "bgcolor=white");
			return $tab->toHTML();
		}

	

} // END COOP VIEW CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP VIEW -->


