<?php 

//$Id$

/*
	Copyright (C) 2004-2005  ken restivo <ken@restivo.org>
	 
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
require_once('object-config.php');
require_once('DB/DataObject/Cast.php');

//////////////////////////////////////////
/////////////////////// COOP FORM CLASS
class coopForm extends CoopObject
{
	var $form;  // cache of generated form
	var $id; // cache of last inserted id
	var $_tableDef; //  cached table stuff

	// i got disgusted with FB. fuck that. i roll my own here.
	function build($id = false)
		{
			$id = (int)$id;
			if($id){
				$this->obj->get($id);
			} else {
				user_error("coopForm::build($id) called with no id, assuming NEW", 
						   E_USER_NOTICE);
			}
			$formname = sprintf('edit_%s', $this->table);
			$form =& new HTML_QuickForm($formname, false, false, false, 
										false, true);
			$this->form = &$form; //  save it so i can dick with it later
			
			$form->addElement('header', $formname, 
							  $this->obj->fb_formHeaderText ?
							  $this->obj->fb_formHeaderText : 
							  ucwords($this->table));

			// will need to guess field types
			$this->_tableDef = $this->obj->table();

			//confessObj($this, 'coopForm::build($id) found');
			foreach($this->obj->toArray() as $key => $val){
				if(!$this->isPermittedField($key)){
					// NOTE the hidden thing. i think  i need to do hidden here
					continue;
				}
				if(is_array($this->obj->fb_preDefElements) && 
				   in_array($key, array_keys($this->obj->fb_preDefElements))){
					$el =& $this->obj->fb_preDefElements[$key];
					$form->addElement($el);
				} else if($this->isLinkField(&$this->obj, $key)){
					$el =& $form->addElement('select', $key, false, 
											 $this->selectOptions($key));
				} else if(is_array($this->obj->fb_textFields) &&
						  in_array($key, $this->obj->fb_textFields))
				{
					$el =& $form->addElement('textarea', $key, false, 
											 array('rows' => 4, 'cols' => 30 ));
				} else if(is_array($this->obj->fb_enumFields) &&
						  in_array($key, $this->obj->fb_enumFields))
				{
					$el =& $form->addElement('select', $key, false, 
											 $this->getEnumOptions($key));
				} else {
					$el =& $form->addElement(
						$key == $this->pk ? 'hidden' : 'text', 
						$key);
				}
				//print $key . "->" .$this->obj->fb_fieldLabels[$key] . "<br>";
				$el->setLabel($this->obj->fb_fieldLabels[$key] ? 
							  $this->obj->fb_fieldLabels[$key] : $key);
				
				if($this->_tableDef[$key] & DB_DATAOBJECT_DATE){
					$form->addRule($key, 
								   'Date must be in format MM/DD/YYYY', 
								   'regex', '/^\d{2}\/\d{2}\/\d{4}$/');
					$val && $val = sql_to_human_date($val);
				}
				
				// finally, pas through default or editable vals
				$el->setValue($val);
			}

			// my hidden tracking stuff
			if($sid = thruAuthCore($this->page->auth)){
				$form->addElement('hidden', 'coop', $sid); 
			}
			
			// XX is this necessary?
			$form->addElement('hidden', 'table', $this->table);

			// finally, sumbit it!
			$form->addElement('submit', null, 'Save');

			$form->applyFilter('__ALL__', 'trim');

			return $form;
		}

	function selectOptions($key)
		{

			// i ALWAYS want a choose one. always. screw FB.
			$options[] = "CHOOSE ONE";
			//confessObj($this, 'this');
			$link = explode(':', $this->forwardLinks[$key]);
			$sub =& new CoopObject(&$this->page, $link[0], &$this);
			$sub->obj->orderBy(implode(', ', 
									   $sub->obj->fb_linkDisplayFields));
			$sub->obj->find();
			while($sub->obj->fetch()){
				$options[(string)$sub->obj->$link[1]] = 
					$this->concatLinkFields(&$sub->obj);
			}

			//TODO: try grabbing the dbresult object instead?
			//confessArray($options, 'oopts');
			return $options;
		}


	// this is called by quickform
	function process($vars)
		{
			
			$this->page->confessArray($vars, 
									  sprintf('CoopForm::process(vars): %s', 
											  $this->table), 
									  2);
	
			$old = $this->obj; // copy, not ref!
			
		
			$this->obj->setFrom($this->scrubForSave($vars));

			// better way to guess?
			if($vars[$this->pk]){		
				$this->update($old);
			} else {
				$this->insert();
			}

					
			return true;
		}


	function scrubForSave($vars)
		{
			$this->_tableDef = $this->obj->table();
			// hack around nulls
			foreach($vars as $key => $val){
				
				// i will be duplicating saveok here, basically
								

				if($val == ''){ 
					$cleanvars[$key] = DB_DataObject_Cast::sql('NULL') ;
				} else if($this->_tableDef[$key] & DB_DATAOBJECT_DATE){
					$cleanvars[$key] = human_to_sql_date($val);
				} else {
					$cleanvars[$key] = $val;
				}

			}
			return $cleanvars;

		}

	
	function update(&$old)
		{
			
			//XXX not needed? $old->get($vars[$this->pk]);
			if(!$old->find(true)){
				user_error("save couldn't get its pk", E_USER_ERROR);
			}

			if($this->page->debug > 1){
				$this->obj->debugLevel(2);
			}
			if($this->page->debug > 3){
				confessObj($old, 'OLD data');
				confessObj($this->obj, 'NEW data');
			}
			$this->obj->update($old);
			$this->id = $this->obj->{$this->pk};
		
			$this->saveAudit(false);
		
			return $this->id;
		}


	function insert()
		{
			if($this->page->debug > 1){
				$this->obj->debugLevel(2);
			}
			$this->obj->insert();
			$this->id = $this->lastInsertID();
			$this->saveAudit(true);
			return $this->id;
		}

	function getEnumOptions($key)
		{
			$db =& $this->obj->getDatabaseConnection();
			$data =& $db->getRow(sprintf("show columns from %s like '$key'",
										 $this->table),
								 DB_FETCHMODE_ASSOC);
			if (DB::isError($data)) {
                die($data->getMessage());
            }
			$this->page->confessArray($data, "coopForm::getEnumOptions($key)", 
									  4);
			preg_match('/enum\((.+?)\)/', $data['Type'], $matches);
			$options = explode(',', ereg_replace("'", "", $matches[1]));
			//array_unshift($options, '-- CHOOSE ONE --');
			
			// TODO set default to , um, default
			
			// selects must stutter: key => val. bah, give me LISP!
			foreach($options as $opt){
				$doubleopt[$opt]  = $opt;
			}
			return $doubleopt;
		}

	function passVarsThrough($varnames, $vars)
		{
			//XXX ack! this overrides what's there. should it?
			// or should it only pass through unset/empty vars?
			foreach($varnames as $varname){
				if($this->form->elementExists($varname)){
					$el =& $this->form->getElement($varname);
					$el->setValue($vars[$varname]);
				} else {
					$this->form->addElement('hidden', $varname, $vars[$varname]);
				}
			}

		}

	// i don't do this in build, i do it later. because for now,
	// the passthrus aren't in here. they should be, arguably tho
	function addRequiredFields()
		{
			if(is_array($this->obj->fb_requiredFields)){
				foreach($this->obj->fb_requiredFields as $fieldname){
// 					user_error("CoopForm::addRequiredFields($fieldname)", 
// 							   E_USER_NOTICE);
					$this->form->addRule($fieldname, 
										 "$key mustn't be empty.", 'required');
				}
			}
		}

} // END COOP FORM CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP FORM -->


