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
require_once 'HTML/QuickForm.php';
require_once('DB/DataObject/FormBuilder.php');
require_once('object-config.php');

//////////////////////////////////////////
/////////////////////// COOP FORM CLASS
class coopForm extends CoopObject
{


	function detailForm($id)
		{
	
			//print_r($this);
			$this->obj->get($id);
            $this->build =& DB_DataObject_FormBuilder::create (&$this->obj);
            //confessObj($this->build, "build");

			// is there a cleanerway to do this? i hate it.
			$hackform = new HTML_QuickForm();
			if($sid =thruAuthCore($this->page->auth)){
				$hackform->addElement('hidden', 'coop', $sid); 
			}
			$hackform->addElement('hidden', 'action', 'process');
			$hackform->addElement('hidden', 'table', $this->table);
			
			$this->build->useForm($hackform);
			$form =& $this->build->getForm();

			$form->applyFilter('__ALL__', 'trim');

            //confessObj($form, "form");
  			//$form->freeze();

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
 			 		//header('Location: ' . $this->selfURL());
				}
				echo "process failed!<br>";
			}

			return $form->toHTML();
	
		}



} // END COOP FORM CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP FORM -->


