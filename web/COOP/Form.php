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
require_once('lib/advmultselect.php');
require_once('lib/customdatebox.php');

//////////////////////////////////////////
/////////////////////// COOP FORM CLASS
class coopForm extends CoopObject
{
	var $form;  // cache of generated form
	var $id; // cache of last inserted id
	var $_tableDef; //  cached table stuff

	// i got disgusted with FB. fuck that. i roll my own here.
	function build($vars = false)
		{
			$this->id = (int)$vars[$this->pk];
			if($this->id > 0){
				$this->obj->get($this->id);
			} else {
				user_error("coopForm::build($this->id) called with no id, assuming NEW", 
						   E_USER_NOTICE);
			}
			$formname = sprintf('edit_%s', $this->table);
			$this->form =& new HTML_QuickForm($formname, false, false, false, 
										false, true);
			
			$this->form->addElement('header', $formname, 
							  $this->obj->fb_formHeaderText ?
							  $this->obj->fb_formHeaderText : 
							  ucwords($this->table));

			// will need to guess field types
			$this->_tableDef = $this->obj->table();

			$this->addAndFillVars($vars);

			// my hidden tracking stuff
			if($sid = thruAuthCore($this->page->auth)){
				$this->form->addElement('hidden', 'coop', $sid); 
			}
			
			// XXX is this necessary? i.e. for new dispatcher
			// if so, bust it out somewhere.
			//$this->form->addElement('hidden', 'table', $this->table);

			//set defaults for new
			if($this->id < 1){
				$this->setDefaults();
			}

			$this->addCrossLinks($vars);

			// finally, sumbit it!
			$this->form->addElement('submit', 'savebutton', 'Save');

			$this->form->applyFilter('__ALL__', 'trim');

			$this->form->addFormRule(array(&$this,'dupeCheck'));

			return $this->form;	// XXX not really necessary?
		}
	
	// yes this is easily as ugly as my old shared.inc, or FB. oh well.
	function addAndFillVars($vars)
		{
			$frozen = array();
			//confessObj($this, 'coopForm::build($id) found');
			foreach($this->obj->toArray() as $key => $dbval){
				// if it's a new entry, fill from vars!
				// this is a clusterfuck because i'm using setValue.
				// otherwise, quickform would do this for me. *sigh*
				// let vars override
				$val = isset($vars[$key]) ? $vars[$key] : $dbval;

				if(!$this->isPermittedField($key)){
					// the hidden thing. i think  i need to do hidden here
					if(is_array($this->obj->fb_requiredFields) && 
					   in_array($key, $this->obj->fb_requiredFields)){
						$this->form->addElement('hidden', $key, $val);
					}
					continue;
				}

				if(is_array($this->obj->fb_preDefElements) && 
				   in_array($key, array_keys($this->obj->fb_preDefElements))){
					$el =& $this->obj->fb_preDefElements[$key];
					$this->form->addElement(&$el);
				} else if($this->isLinkField(&$this->obj, $key)){
					$el =& $this->form->addElement('select', $key, false, 
											 $this->selectOptions($key));
				} else if(is_array($this->obj->fb_textFields) &&
						  in_array($key, $this->obj->fb_textFields))
				{
					$el =& $this->form->addElement('textarea', $key, false, 
											 array('rows' => 4, 
												   'cols' => 30 ));
				} else if(is_array($this->obj->fb_enumFields) &&
						  in_array($key, $this->obj->fb_enumFields))
				{
					$el =& $this->form->addElement('select', $key, false, 
											 $this->getEnumOptions($key));
				} else if(is_array($this->obj->fb_booleanFields) &&
						  in_array($key, $this->obj->fb_booleanFields))
				{
					$el =& $this->form->addElement('advcheckbox', $key);
				} else if($this->_tableDef[$key] & DB_DATAOBJECT_DATE){
					$el =& $this->form->addElement('customdatebox', $key);
					$this->form->addRule($key, 
								   'Date must be in format MM/DD/YYYY', 
								   'regex', '/^\d{2}\/\d{2}\/\d{4}$/');
					$val && $val = sql_to_human_date($val);
				} else {
					//i ALWAYS hide primary key. it's hardcoded here.
					// note this is different from FB behaviour.
					// XXX this is broken. i need to deal with fb_hidePrimaryKey
					$el =& $this->form->addElement(
						$key == $this->pk ? 'hidden' : 'text', 
						$key);
				}
				//print $key . "->" .$this->obj->fb_fieldLabels[$key] . "<br>";
				$el->setLabel($this->obj->fb_fieldLabels[$key] ? 
							  $this->obj->fb_fieldLabels[$key] : $key);
				
				
				// finally, pas through default or editable vals
				$el->setValue($val);

				if(is_array($this->obj->fb_userEditableFields) &&
 							!in_array($key, 
 									  $this->obj->fb_userEditableFields))
				{
					$frozen[] = $key;
				}

			}
			$this->page->confessArray($frozen, 
									  'CoopForm::addAndFillVars(fruzen gladje)',
									  3);			
			$this->form->freeze($frozen);
		}

	function selectOptions($key)
		{

			// i ALWAYS want a choose one. always. screw FB.
			$options[] = "-- CHOOSE ONE --";
			//confessObj($this, 'this');
			$link = explode(':', $this->forwardLinks[$key]);
			$sub =& new CoopObject(&$this->page, $link[0], &$this);
			if(is_callable(array($sub->obj, 'fb_linkConstraints'))){
				$sub->obj->fb_linkConstraints();
			} else {
				$sub->defaultConstraints();
			}
			//TODO add linkorderfields
			foreach($sub->obj->fb_linkDisplayFields as $field){
				$ldf[] = sprintf("%s.%s", $sub->table, $field);
			}
			$sub->obj->orderBy(implode(', ', $ldf));
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
			
			$this->processCrossLinks($vars);
			
			$this->enema($vars); // necessary. *sigh*
					
			return "<h3>Entry was successful!</h3>";
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
			
			if(!$old->find(true)){
				PEAR::raiseError("save couldn't get its pk. did something else change the record in between editing and saving?", 888);
			}

			if($this->page->debug > 1){
				$this->obj->debugLevel(2);
			}
			if($this->page->debug > 2){
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

	/// XXX THIS SUCKS> it's only used in RSVP. remove later.
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


	function setDefaults()
		{
			if(!is_array($this->obj->fb_defaults)){
				return;
			}

			$this->form->setDefaults($this->obj->fb_defaults);
		}

	function processCrossLinks($vars)
		{
			//confessObj($this, 'this');
			print $this->page->confessArray($vars, 
									  'CoopForm::processAddRemove(vars)', 1);
			
			if(!is_array($this->obj->fb_crossLinks)){
				return;
			}
			//$this->obj->debugLevel(0); 
			// for everything in crosslinks, 
			foreach($this->obj->fb_crossLinks as $la){
				$tf = $la['toField'];
				$mt = $la['table'];
				$ft = $la['toTable'];
				$nk = $this->backlinks[$mt];
				if(!($tf && $mt && $ft && $nk)){
					PEAR::raiseError('your crosslinks spec sucks', 777);
				}
				
 				if(!isset($vars[$tf])){
					// XXX this scares me. if i forget to include these...
					// then they get wiped out of the db? that seems wrong to me.
					$vars[$tf] = array();
 					//continue;
 				}

				//print "mt $mt tf $tf ft $ft nk $nk";
				
				// yeah, array_diff is the long way with db thrashing.
				// but i want clear, easily-debugged code
				$this->obj->{$this->pk} = $this->id;
				$indb = $this->checkCrossLinks($mt,$ft);
				$this->page->confessArray($indb, 
										  'CoopForm::processCrossLinks(indb)');
				$toSave = array_diff($vars[$tf], $indb);
				if(count($vars[$tf]) < count($indb)){
					$toDelete = array_diff($indb, $vars[$tf]);
				}
				$this->page->confessArray($toSave, 
										  'CoopForm::processsCrossLinks(save)', 
										  2);
				$this->page->confessArray($toDelete, 
										  'CoopForm::processsCrossLinks(delete)',
										  2);


				//$this->obj->debugLevel(2);
				// save
				if(is_array($toSave)){
					foreach($toSave as $saveme){
						$mid =& new CoopObject(&$this->page, $mt, &$this);
						$mid->obj->$tf = $saveme;
						$mid->obj->$nk = $this->id;
						$mid->obj->insert();
					}
				}
				
				// delete
				if(is_array($toDelete)){
					foreach($toDelete as $killme){
						$mid =& new CoopObject(&$this->page, $mt, &$this);
						$mid->obj->$tf = $killme;
						$mid->obj->$nk = $this->id;
						$mid->obj->limit(1);
						$mid->obj->delete();
					}
				}

			}
		}

	function addCrossLinks($vars)
		{
			
			if(!is_array($this->obj->fb_crossLinks)){
				return;
			}

			foreach($this->obj->fb_crossLinks as $la){
				$tf = $la['toField'];
				$mt = $la['table'];
				$ft = $la['toTable'];
				$nk = $this->backlinks[$mt];

				
				//duplication of selectoptions
				$this->page->debug > 3 && $this->obj->debugLevel(2);
				$far = new CoopObject(&$this->page, $ft, $this);

				// IIRC this has a different name in FB. use theirs!
 				if(is_callable(array($far->obj, 'fb_linkConstraints'))){
					$far->obj->fb_linkConstraints();
				} else {
					$far->defaultConstraints();
				}
				$far->obj->orderBy(implode(', ', 
										   $far->obj->fb_linkDisplayFields));
				$far->obj->find();
				while($far->obj->fetch()){
					$options[(string)$far->obj->$tf] = 
							 sprintf('%.42s...', 
									 $this->concatLinkFields(&$far->obj));
				}
				
	
				$this->obj->{$this->pk} = $this->id; // for checkcrosslinks
				if(isset($vars[$tf])){
					$incl = $vars[$tf];
				} else {
					$incl = $this->checkCrossLinks($mt, $ft);
				}
				$this->page->confessArray($incl, 
										  'CoopForm::addCrossLinks(incl)', 2);

				// HACK! MUST sanity czech that indb is in options, and push
				$optkeys = array_keys($options);
				foreach($incl as $key ){
					if(!in_array($key, $optkeys)){
						$far = new CoopObject(&$this->page, $ft, $this);
						$far->obj->$tf = $key;
						$far->obj->find(true);
						$options[$key] = 
							 sprintf('%.42s...', 
									 $this->concatLinkFields(&$far->obj));
					}
				}
				
				// jeebus. all this, just to get to here.
				$el =& $this->form->addElement('advmultselect', 
										$tf,
										$far->title(),
										$options);
 				$el->setValue($this->isSubmitted ? $_REQUEST[$tf] : $incl);
				
			}
		}

	function isSubmitted()
		{
			return isset($_REQUEST['_qf__' . $this->form->_attributes['name']]);
		}

	function legacyPassThru()
		{
			// ugly assthrus for my old-style dispatcher
			// XXX these conflict with the new dispatcher!
			$this->form->addElement('hidden', $this->pk, 
									$this->obj->{$this->pk}); 

		}
	
	function dupeCheck($vars)
		{
			if($vars[$this->pk] > 0){
				return true;	// i'm editing. no dupecheck on edit.
			}

			//TODO check for a dupecheck function in the object, and use that
			// then revert to this as a default
			// also check for a list of dupeignore fields. hmm. which easier?
			$temp =& new CoopObject(&$this->page, $this->table, &$this);
			$foo = $temp->obj;
			$foo->whereAdd();
			$this->page->debug > 2 && $foo->debugLevel(2);
			$ov = array_keys(get_object_vars($foo));
			foreach($vars as $key => $var){
				// XXX hack, cough, gaaack.
				if(in_array($key, $ov) && $key != $this->pk && $var){
					if(!is_numeric($var)){
						$var = sprintf("'%s'", $foo->escape($var));
					}
					$foo->whereAdd(sprintf("%s = %s", 
										   $key, $var));
				}
			}
			//confessObj($foo, 'foo');
			if($foo->find(true)){
				foreach($vars as $key => $val){
					if($val == $foo->$key){
						$duples[$key] = 'Duplicate entry.';
					}
				}
				return $duples ;
			}
			return true;
		}
			

	// cleans out REQUEST after a save! vital for re-displaying new.
	function enema($vars)
		{
			$ov = array_keys(get_object_vars($this->obj));
 			foreach($ov as $key){
 				unset($_REQUEST[$key]);
 			}
			// i have to hack qf tracksumbit here.
			// otherwise it puts old data back!
			unset($_REQUEST['_qf__' . $this->form->_attributes['name']]);
			
			// now the xlinks too
			if(!is_array($this->obj->fb_crossLinks)){
				return;
			}
			foreach($this->obj->fb_crossLinks as $la){
				unset($_REQUEST[$la['toField']]);
			}
		}


} // END COOP FORM CLASS

////KEEP EVERTHANG BELOW

?>
<!-- END COOP FORM -->


