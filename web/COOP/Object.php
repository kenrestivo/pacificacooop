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
			if(!is_object($page) || !$table){
				user_error("coopObject: blank page or table object passed", 
						   E_USER_ERROR);
			}

			$this->page = $page;
			$this->table = $table ;
			$this->recurseLevel = $level;
			$this->parentCO = $parentCO;
			
 			$this->obj = DB_DataObject::factory($this->table); // & instead?
			if (PEAR::isError($this->obj)){
				$this->page->kensPEARErrorHandler(&$this->obj);
				 user_error("coopObject::constructor: " . 
							$this->obj->getMessage(),
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
									  "getLinks: links for $this->table",
									  4);

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
									  "getLinks() backlinks for $this->table",
									  4);
			return $this->backlinks;
		}

	function isLinkField(&$obj, $key)
		{
			// and only if, um, the links.ini agrees that they are there
			$links = $obj->links();
			if(!$links){
				//print "no links for $this->table $key $val<br>";
				return false;
			}
			$this->page->confessArray($links, 
									  "checkLInkField(): links for $this->table", 4);

			if(!$links[$key]){
				return false;
			}
			
			
			return true;

		}


	//  inspired by formbuilder's getdataobjctselectdisplayvalue (whew!)
	function checkLinkField(&$obj, $key, $val)
		{
		
			if(!$this->isLinkField($obj, $key)){
				//user_error("$this->table $key is not a linkfield", 
				//		E_USER_NOTICE);
				return $val;
			}

			// don't bother with blanks either
			if(!$obj->$key){
				return false;
			}

			//ok, we have run the fucking gauntlet here.
			//confessObj($obj, 
//					   "checkLinkField() from $this->table: obj with links for $key of $val");

			$subobj = $obj->getLink($key); 
	//confessObj($subobj, "checkLInkFild() subobj $subobj->__table for $key of $val");


			// remember, subobj does NOT have a concatlinkfields method
			// so i am keepign the method here and passing the subobj
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

	function saveAudit($insert = true)
		{
			// don't save audits for joins dude
			if(ereg("_join", $this->table)){
				return;
			}
			
			// the audit trail dude
			// TODO copy/paste this later into my framework
			$aud =& new CoopObject(&$this->page, 'audit_trail', &$top);
			$aud->obj->table_name = $this->table;

			if($insert){
				// NO CONFIDENcE  in DBDO. use lastinsert instead!
// 				if($this->obj->{$this->pk} != $this->lastInsertID()){
// 					user_error("last insert != object's PK! this shouldn't happen",
// 							   E_USER_ERROR);
// 				}
				$aud->obj->index_id = $this->lastInsertID();
			} else {
				$aud->obj->index_id = $this->obj->{$this->pk};
			}
			
			// one more sanity czech
			if(!$aud->obj->index_id){
				$this->page->mailError('NULL audit trail attempt', 
									   print_r($this, true));
				user_error("something very bad happened when saving audit trail. index id can't be null.",
						   E_USER_ERROR);
			}

			$aud->obj->audit_user_id = $this->page->auth['uid'];
			$aud->obj->insert();
		}

	function isPermittedField($key)
		{
			// if it's a key, and we don't show them, then no
			if($key == $this->pk && $this->obj->fb_hidePrimaryKey){
				return false;
			}
			//we don't show if not in fieldstorender
			if($this->obj->fb_fieldsToRender && 
			   !in_array($key, $this->obj->fb_fieldsToRender)){
				return false;
			}

			// TODO: check user permissions!
			
//			confessArray($this->obj->fb_fieldsToRender, "$key is in:");
			return true;
		}

	// so commonly used, i need it here
	// gets the crosslinks FOR THIS OBJECT
	// the index MUST NOT be null.
	function checkCrossLinks($midTable, $farTable)
		{
 			if($this->obj->{$this->pk} < 1){
 				return array();
 			}

			$included = array(); // must return array even if empty!
			// TODO: maybe try to calculate mid or fartable? dangerous?
			$far = new CoopObject(&$this->page, $farTable, $this);
			//confessObj($this, 'CoopObject::checkCrossLinks(this)');
			$this->page->debug > 2 && $far->obj->debugLevel(2);
			$mid =& new CoopObject(&$this->page, $midTable, &$far);
			$mid->obj->{$this->pk} = $this->obj->{$this->pk};
			$far->obj->joinAdd($mid->obj);
			$far->obj->orderBy(sprintf("%s.%s", $far->table, $far->pk));
			if($far->obj->find()){
				$included = array();
				while($far->obj->fetch()){
					$included[] = $far->obj->{$far->pk};
				}
			}
			return $included;
		}

	function title()
		{
			if($this->obj->fb_formHeaderText){ 
				return $this->obj->fb_formHeaderText;
			}
			return ucwords($this->table);
		}
	
} // END COOP OBJECT CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP OBJECT -->


