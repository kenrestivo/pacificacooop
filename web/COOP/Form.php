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
require_once('object-config.php');

//////////////////////////////////////////
/////////////////////// COOP FORM CLASS
class coopForm extends CoopObject
{



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



} // END COOP FORM CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP FORM -->


