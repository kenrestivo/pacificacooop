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
	var $pk;					// the primary key. convenience, really.
	var $recurseLevel;			// level, if i'm linked from somewhere
	var $parentCO;				// reference. "parent" is reserved word
	var $backlinks;				// list of links that are linked FROM here
	var $forwardLinks;

	function CoopObject (&$page, $table, &$parentCO, $level = 0)
		{

			$this->page = $page;
			$this->table = $table ;
			$this->recurseLevel = $level;
			$this->parentCO = $parentCO;
			
			$this->obj =& DB_DataObject::factory ($this->table); // & instead?
			if (PEAR::isError($obj)){
				die ($obj->getMessage ());
			}
			//confessObj($this->obj, "CONSTRUCTOR object for $this->table");

			$this->getPK(); // must this be after find? rather constructor.
				
		}
 


	function getPK()
		{
			$keys = $this->obj->keys();
			$this->pk = $keys[0];
			return $this->pk;
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

	//  inspired by formbuilder's getdataobjctselectdisplayvalue (whew!)
	function checkLinkField(&$obj, $key, $val)
		{
		
			
			// and only if, um, the links.ini agrees that they are there
			$links = $obj->links();
			if(!$links){
				//print "no links for $this->table $key $val<br>";
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
					$val .= sprintf("%s%s", $val ? ' - ' : "", $obj->$linkfield);
				}
			}

			return $val;
		}


	function getSummary()
		{
			
			// XXX hack to skip over jointables. ugly, but it works.
			if(preg_match('/_join/', $this->table, $matches)){
				return $this->parentCO->getSummary();
			}
			//confessObj($view, "companyDetails(view)");
			return $this->concatLinkFields(&$this->obj);
				
		}

} // END COOP OBJECT CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP OBJECT -->


