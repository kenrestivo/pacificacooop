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
require_once 'HTML/QuickForm.php';
require_once('DB/DataObject/FormBuilder.php');
require_once('Pager/Pager.php');
require_once('object-config.php');

//////////////////////////////////////////
/////////////////////// COOP VIEW CLASS
class coopView
{
	var $obj;
	var $build;
	var $page;
	var $pager_result_size;
	var $pager_start;
	var $table;
	var $pk;
	var $backlinks;
	var $recurseLevel;

	function CoopView (&$page, $table, $level = 0 )
		{

			$this->page = $page;
			$this->table = $table ;
			$this->recurseLevel = $level;
			
			$this->obj =& DB_DataObject::factory ($this->table); // & instead?
			if (PEAR::isError($obj)){
				die ($obj->getMessage ());
			}
			//confessObj($this->obj, "object for $this->table");
				
		}
 


	// returns an action structure: tables[tablename][action], etc
	function getBackLinks()
		{
			
//			confessObj($this, "view");
			global $_DB_DATAOBJECT;
			//confessObj($_DB_DATAOBJECT, "dataobject");
			$tab =  $this->obj->tableName();
			$links = $_DB_DATAOBJECT['LINKS']['coop']; // XXX hard code hack! 
			//$this->page->confessArray($links, "links");
			foreach($links as $maintable => $link){
				foreach ($link as $nearcol => $farline){
					// split up farline and chzech it
					list($fartable, $farcol) = explode(':', $farline);
					if($fartable == $tab){
						$res[$maintable] = $nearcol;
					}
				}
			}
			$this->backlinks = $res;
			$this->page->confessArray($res,"backlinks");
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
				$subview =& new CoopView($this->page, $backtable, $this->recurseLevel + 1);
				$subview->obj->$farkey = $this->obj->$nearkey;
// 				printf("linking %s.%s to %s.%s<br>", 
// 					   $this->table, $this->pk, 
// 					   $backtable, $farkey);
				$this->addSubTable(&$tab, $subview->recurseTable());
			}
		}


	function getPK()
		{
			$keys = $this->obj->keys();
			$this->pk = $keys[0];
			return $this->pk;
	}


	//  inspired by formbuilder's getdataobjctselectdisplayvalue (whew!)
	function checkLinkField(&$obj, $key, $val)
		{
		
			
			// and only if, um, the links.ini agrees that they are there
			$links = $obj->links();
			if(!$links){
				return $val;
			}
			$this->page->confessArray($links, "links for $this->table");

			if(!$links[$key]){
				return $val;
			}

			//ok, we have run the fucking gauntlet here.
			//confessObj($obj, 
//					   "from $this->table: obj with links for $key of $val");
			$subobj = $obj->getLink($key); 
	//confessObj($subobj, "subobj $subobj->__table for $key of $val");

				// only if i have linkfields in the dataobj
			$ldfs = $subobj->fb_linkDisplayFields;
			if(!$ldfs){
				return $val;
			}
			$val = false; 		// gotta reset it here.
			foreach($ldfs as $linkfield){
				// trying to YAGNI here. i don't need 2-level links yet
				// so, i'm not coding that recursion in here now. sorry charlie.
				$val .= sprintf("%s%s", $val ? ' - ' : "", $subobj->$linkfield);
			}

			return $val;
		}

	function toArray()
		{
			
			foreach($this->obj->toArray() as $key => $val){
				// this is where the fun begins.
				$res[] = $this->checkLinkField(&$this->obj, $key, $val);

			}

			// the Simple Version. useful for debuggin'
			//return array_values($this->obj->toArray());

			//$this->page->confessArray($res, "resR array");
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

	function addTableTitle(&$tab)
		{
			$index = 
			$this->addSubTable(&$tab, 
								   sprintf("%s: for %s", 
										   $this->table, 
										   $this->obj->family_id));
	
		}

	function recurseTable()
		{
			$found = $this->obj->find();
			
			if($found < 1){
				return false;
			}

			$this->getBackLinks();	// MUST be after find!

			$this->getPK(); // must this be after find? rather constructor.

			$jointable = preg_match('/_join/', $this->table);

			$tab =& new HTML_Table();

			// TODO:  forbidden names XXX nasty hack
			if(!$jointable){
				$this->addTableTitle(&$tab);
				$this->addHeader(&$tab);
			}
			while($this->obj->fetch()){
				// the main row.
				if(!$jointable){
					$tab->addRow($this->toArray());
				}
				//subrows
				$this->addSubTables(&$tab, $pk, $backlinks);

			}

			$tab->altRowAttributes(1, "bgcolor=#CCCCC", "bgcolor=white");
			return $tab->toHTML();
		}

	

} // END COOP VIEW CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP VIEW -->


