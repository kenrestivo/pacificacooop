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
	var $recurseLevel;			// level, if i'm linked from somewhere
	var $parentCO;				// reference. "parent" is reserved word
	var $backlinks;				// list of links that are linked FROM here
	var $forwardLinks;

	function CoopObject (&$page, $table, &$parentCO, $level = 0)
		{
			if(!$page || !$table){
				user_error("coopObject: blank page or table object passed", 
						   E_USER_ERROR);
			}

			$this->page = $page;
			$this->table = $table ;
			$this->recurseLevel = $level;
			$this->parentCO = $parentCO;
			
			$this->obj =& DB_DataObject::factory($this->table); // & instead?
			if (PEAR::isError($obj)){
				 user_error("coopObject::constructor: " . $obj->getMessage(),
							E_USER_ERROR);
			}
			//confessObj($this->obj, "CONSTRUCTOR object for $this->table");

			$this->getPK(); // must this be after find? rather constructor.
			$this->getLinks();
		}
 


	function getPK()
		{
			$keys = $this->obj->keys();
			$this->pk = $keys[0];
			return $this->pk;
	}

	// different from the object's getlinks! this one gets my backlinks
	function getLinks()
		{
			
//			confessObj($this, "view");
			global $_DB_DATAOBJECT;
			//confessObj($_DB_DATAOBJECT, "getLinks() dataobject");
			$links =& $_DB_DATAOBJECT['LINKS'][$this->obj->database()];

			$this->page->confessArray($links, 
									  "getLinks: links for $this->table");

			$this->forwardLinks = $links[$this->table];

			foreach($links as $maintable => $mainlinks){
				foreach ($mainlinks as $nearcol => $farline){
					// split up farline and chzech it
					list($fartable, $farcol) = explode(':', $farline);
					if($fartable == $this->table){
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
			
			if(!$obj->$key){
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

	// returns the IMMEDIATE parent, not including joins
	function getParent()
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
			//confessObj($view, "companyDetails(view)");
			return $this->concatLinkFields(&$this->obj);
				
		}

    function lastInsertID()
        {
            $db =& $this->obj->getDatabaseConnection();

            $data =& $db->getOne('select last_insert_id()');
            if (DB::isError($data)) {
                die($data->getMessage());
            }
            return $data;
        }

	// recurses through parents, until it finds the top!
	function findTop()
		{
			if($this->parentCO){
				return $this->parentCO->findTop();
			}
			return($this);
		}


} // END COOP OBJECT CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP OBJECT -->


