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

	function CoopView (&$page, $table )
		{

			$this->page = $page;
			$this->table = $table ;
			
			$this->obj =& DB_DataObject::factory ($this->table); // & instead?
			if (PEAR::isError($obj)){
				die ($obj->getMessage ());
			}
			confessObj($this->obj, "object for $this->table");
		
		}
 

	// global defaults, common through *all* forms, so not in objs
	function setFormDefaults(&$form)
		{
			// start with schoolyear
			//confessObj($sy, "element");
			$sy =& $form->getElement('school_year');
			if($sy->getValue() == ""){
				$sy->setValue(findSchoolYear());
			}
		}


	function detailForm($id = false )
		{
	
			//print_r($this);
			$id = $id ? $id : $_SESSION[$this->table]['id'];
			$this->obj->get($id);
            $this->build =& DB_DataObject_FormBuilder::create (&$this->obj);
            //confessObj($this->build, "build");
			$this->obj->fb_createSubmit = false;
            $form =& new HTML_QuickForm(); 
			$form->addElement('html', thruAuth($page->auth, 1));
			$buttons[] = &HTML_QuickForm::createElement(
					'submit', 'cancel', 'Cancel');
			$buttons[] = &HTML_QuickForm::createElement(
				'submit', '__submit__', 'Save');
			$form->addGroup($buttons, null, null, '&nbsp;');


            $this->build->useForm($form);
			$form =& $this->build->getForm();
			$form->applyFilter('__ALL__', 'trim');
            //confessObj($form, "form");
  			//$form->freeze();
			// XXX BROKEN FUCK FUCK FUCK FUCK $this->setFormDefaults(&$form);
			$this->getBackLinks();
			if($form->validate ()){
				$res = $form->process (array 
									   (&$this->build, 'processForm'), 
									   false);
				if ($res){
					$this->obj->debug('processed successfully', 
								'detailform', 0);
					saveAudit($this->table, $id, $page->auth['uid']);
					// XXX make sure i don't have to unset id's first!
					///  next action
					print "PRICESSING SEUCCSSCUL";
					$_SESSION['tables'][$this->table]['action'] = 'list'; 
 			 		header('Location: ' . $this->selfURL());
				}
				echo "AAAAUUUUUUUUUUUGGH!<br>";
			}

			return $form->toHTML();
	
		}


	// returns an action structure: tables[tablename][action], etc
	function getBackLinks()
		{
			
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
						$res[] = $maintable;
					}
				}
			}
			$this->page->confessArray($res,"backlinks");
			return $res;
		}
	
	// formats object is current in this object, um, as a table
	function simpleTable()
		{
			$this->obj->find();
			//TODO return null or something, if nothing found
			$tab =& new HTML_Table();
			while($this->obj->fetch()){
				$tab->addRow(array_values($this->obj->toArray()));
			
			}
			return $tab->toHTML();
		}

	function addSubTables($backlinks)
		{
			
			$subview =& new CoopView(&$page, 
									 'companies_auction_join');
			$subview->obj->company_id = $this->obj->company_id;
			return  "SHIT"; //$subview->simpleTable();
		}
	
	
	function recurseTable()
		{

			$this->obj->find();

			$backlinks = $this->getBackLinks();	// MUST be after find!

			//TODO return null or something, if nothing found
			$tab =& new HTML_Table();
			while($this->obj->fetch()){
				// the main row.
				$tab->addRow(array_values($this->obj->toArray()));
				
				// sub rows
				$tab->addRow(array($this->addSubTables($backlinks)));				
				$colcount = $tab->getColCount();
				$rowcount = $tab->getRowCount() - 1; // zero based
				$tab->setCellContents($rowcount, 0, 
									  "ROW $rowcount COl $colcount");
				//might first need to getcellatrs, then add to array
				$tab->setCellAttributes($rowcount,0,"colspan=$colcount");

			}
			return $tab->toHTML();
		}
	

} // END COOP VIEW CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP VIEW -->


