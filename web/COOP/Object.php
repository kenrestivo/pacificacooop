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
/////////////////////// COOP OBJECT CLASS
class coopObject
{
	var $obj;					// ref to db dataobject for this view
	var $page;					// reference to the cooppage
	var $table;					// convenience: the table the $this->obj is
	var $pk;					// the primary key
	var $recurseLevel;			// level, if i'm linked from somewhere
	var $parentObj;				// reference to parent object

	function CoopObject (&$page, $table, $level = 0, $parent = NULL )
		{

			$this->page = $page;
			$this->table = $table ;
			$this->recurseLevel = $level;
			$this->parentObj = $parent;
			
			$this->obj =& DB_DataObject::factory ($this->table); // & instead?
			if (PEAR::isError($obj)){
				die ($obj->getMessage ());
			}
			//confessObj($this->obj, "CONSTRUCTOR object for $this->table");
				
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
			$this->page->confessArray($links, 
									  "checkLInkField(): links for $this->table");

			if(!$links[$key]){
				return $val;
			}

			//ok, we have run the fucking gauntlet here.
			//confessObj($obj, 
//					   "checkLinkField() from $this->table: obj with links for $key of $val");
			$subobj = $obj->getLink($key); 
	//confessObj($subobj, "checkLInkFild() subobj $subobj->__table for $key of $val");

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

	

} // END COOP OBJECT CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP VIEW -->


