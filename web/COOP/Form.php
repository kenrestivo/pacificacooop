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

require_once('CoopPage.php');
require_once('CoopObject.php');
require_once('DB/DataObject.php');
require_once 'HTML/QuickForm.php';
require_once('object-config.php');
require_once('DB/DataObject/Cast.php');
require_once('lib/advmultselect.php');
require_once('lib/customselect.php');
require_once('lib/searchselect.php');
require_once('lib/customdatebox.php');
require_once('lib/customrequired.php');

//////////////////////////////////////////
/////////////////////// COOP FORM CLASS
class coopForm extends CoopObject
{
	var $form;  // cache of generated form
	var $_tableDef; //  cached table stuff



	// i got disgusted with FB. fuck that. i roll my own here.
	function &build($vars = false)
		{
			$this->page->confessArray($vars, "CoopForm::build({$this->table})", 3);
			$this->id = (int)$vars[$this->prependTable($this->pk)];
			if($this->id > 0){
				$this->obj->get($this->id);
			} else {
				$this->page->printDebug(
                    "coopForm::build($this->table $this->id) called with no id, assuming NEW");
                
			}
			$formname = sprintf('edit_%s', $this->table);
			if(!$this->form){	// deal with QFC
				$this->form =& new HTML_QuickForm($formname, false, false, 
												  false, false, true);
			}
			$this->form->addElement('header', $formname, 
							  $this->obj->fb_formHeaderText ?
							  $this->obj->fb_formHeaderText : 
							  ucwords($this->table));

			// will need to guess field types
			$this->_tableDef = $this->obj->table();

            // NOTE! i must do this before calling generators
            $this->form->CoopForm =& $this; 

            if(is_callable(array($this->obj, 'preGenerateForm'))){
                $this->obj->preGenerateForm(&$this->form);
            }
            
            

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
			if(!isset($this->obj->fb_createSubmit) ||
			   $this->obj->fb_createSubmit > 0)
			{
				$this->form->addElement('submit', 'savebutton', 'Save');
                // XXX this cancel link is brain-dead
                // it needs to go BACK, to whatever called this, stack
                $this->form->addElement(
                    'static', 
                    'cancel', '', 
                    $this->page->selfURL(
                        array('value' => 'Cancel',
                              'inside' => array(
                                  'pop' => $this->table))));
			}

			$this->form->applyFilter('__ALL__', 'trim');

			$this->form->addFormRule(array(&$this,'dupeCheck'));
            

            //godDAMN do i hate php.
            if(is_callable(array($this->obj, 'postGenerateForm'))){
                $this->page->printDebug(
                    "calling {$this->table}::postgenerateForm", 2);
                $this->obj->postGenerateForm(&$this->form);
            }
            
            $this->page->confessArray($this->form->_rules, 
                                      'built  rules', 5);
            $this->page->confessArray($this->form->_formRules, 
                                      'built  formrules', 5);

			return $this->form;	// XXX not really necessary?
		}
	
	// yes this is easily as ugly as my old shared.inc, or FB. oh well.
	function addAndFillVars($vars)
		{

			// need these for un-html'ing
			$trans_tbl = get_html_translation_table (HTML_ENTITIES);
			$trans_tbl = array_flip ($trans_tbl);       



			$frozen = array();
			//confessObj($this, 'coopForm::build($id) found');
			foreach($this->obj->toArray() as $key => $dbval){
				// deal with tablenamign these thigns
				$fullkey = $this->prependTable($key);
                $this->page->printDebug(
                    "--- starting addandfillvars for $fullkey--", 4);

                $perms = $this->isPermittedField($key, !$this->id, !$this->id);

				// if it's a new entry, fill from vars!
				// this is a clusterfuck because i'm using setValue.
				// otherwise, quickform would do this for me. *sigh*
				// let vars override
				if(isset($vars[$fullkey])){ 
					// setting from USER INPUT
					// what does QF do to pre-process?
                    $this->page->printDebug(
                        "CoopForm::addandfillvars() setting [$fullkey] to [{$vars[$fullkey]}] from user input vars passed to build()",  4);
					$val = strtr($vars[$fullkey], $trans_tbl);  
				} else {
					// setting from SQL DATABASE VALUES
					// the key question is: what does QF do b4 displaying?
					$val = $dbval;
				}

                // rememebr !id means it is a NEW/ADD
                if($perms < ACCESS_VIEW || 
                   (!$this->id && $perms < ACCESS_ADD))
                {
					// required, OR the special magic fields schoolyear/familyid
					if((!empty($this->obj->fb_requiredFields) && 
					   in_array($key, $this->obj->fb_requiredFields)) ||
                        $key == 'school_year' || $key == 'family_id')
                    {
						$this->form->addElement('hidden', $fullkey, $val);
					}
					continue;
				}

				if(!empty($this->obj->fb_preDefElements) && 
				   in_array($key, array_keys($this->obj->fb_preDefElements)))
                {
					$el =& $this->obj->fb_preDefElements[$key];
					$this->form->addElement(&$el);
					$el->setName($fullkey); 
				} else if($this->isLinkField($key)) {
                    $type = 'select';
                    // default all linkfields are customeselect, unless i hack
                    if(empty($this->obj->fb_addNewLinkFields) ||
                       in_array($key, $this->obj->fb_addNewLinkFields))
                    { 
                        $type = 'customselect';
                        //XXX hack until i test for N found
                        if(!empty($this->obj->fb_searchSelects) &&
                            in_array($key, $this->obj->fb_searchSelects))
                        {
                            $type = 'searchselect';
                        }
                        $this->page->printDebug("$key is a $type", 4);
                    }
                    $el =& $this->form->addElement(
                        $type, 
                        $fullkey, false);
                    if($type != 'searchselect'){
                        $tmp = $this->findLinkOptions($key);
                        // TODO: check $tmp[0]->obj->N,
                        //  call searchselect if > lots
                        $el->loadArray(call_user_func_array(
                                           array($this, 'getLinkOptions'),
                                           $tmp));
                    }
                    //XXX parentform isn't available at add, only at toHTML
                    $el->_parentForm =& $this->form;
						
				} else if(!empty($this->obj->fb_textFields) &&
						  in_array($key, $this->obj->fb_textFields))
				{
                    $cols = !empty($this->obj->fb_sizes[$key]) ?
                        $this->obj->fb_sizes[$key] : 40;
					$el =& $this->form->addElement('textarea', 
                                                   $fullkey, false, 
                                                   array('rows' => 10,  //??
                                                         'cols' => $cols));
				} else if(!empty($this->obj->fb_enumFields) &&
						  in_array($key, $this->obj->fb_enumFields))
				{
					$el =& $this->form->addElement('select', $fullkey, false, 
											 $this->getEnumOptions($key));

				} else if($key == 'school_year') {
                    // handle schoolyears speciaally!
					$el =& $this->form->addElement('select', $fullkey, false, 
											 $this->getSchoolYears($val));


				} else if(!empty($this->obj->fb_booleanFields) &&
						  in_array($key, $this->obj->fb_booleanFields))
				{
					$el =& $this->form->addElement('advcheckbox', $fullkey);
				} else if($this->_tableDef[$key] & DB_DATAOBJECT_DATE){
					$el =& $this->form->addElement('customdatebox', $fullkey);
                    if($this->_tableDef[$key] & DB_DATAOBJECT_TIME){
                        // NOTE! regexp must match utils.inc:humantosqldate
                        $this->form->addRule( 
                           $fullkey, 
                            'Date must be in format MM/DD/YYYY HH:MM', 
                           'regex', 
                           '/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s*?((\d{1,2}):(\d{2})(:\d{1,2})*)*\s*(\w{2})*$/',
                           'client');
                        $val && $val = timestamp_db_php($val);
                    }else{
                        $this->form->addRule(
                            $fullkey, 
                            'Date must be in format MM/DD/YYYY', 
                            'regex', '/^\d{2}\/\d{2}\/\d{2,4}$/',
                            'client');
                        $val && $val = sql_to_human_date($val);
                    }

				} else if($this->_tableDef[$key] & DB_DATAOBJECT_BOOL){
					$el =& $this->form->addElement('advcheckbox', $fullkey, 
                                                   null, null, null, 
                                                   array(0,1));
				} else {
                    // ok, it's just text
					$el =& $this->form->addElement('text', $fullkey);
                    if(is_array($this->obj->fb_sizes) &&
                       !empty($this->obj->fb_sizes[$key]))
                    {
                        $el->setSize($this->obj->fb_sizes[$key]);
                    }
				}
				//print $key . "->" .$this->obj->fb_fieldLabels[$key] . "<br>";
				// TODO: uppercase this thing, replace _ with spaces
				$el->setLabel($this->obj->fb_fieldLabels[$key] ? 
							  $this->obj->fb_fieldLabels[$key] : $key);
				
				
				// finally, pas through default or editable vals
                $this->page->printDebug(
                    'addandfillvars() calling '. $el->getName() .
                    "->setvalue($val)", 2);
				$el->setValue($val);

				if(is_array($this->obj->fb_userEditableFields) &&
 							!in_array($key, 
 									  $this->obj->fb_userEditableFields))
				{
					$frozen[] = $fullkey;
				}

                //ok, perms stuff here now
                // i only need 
                if($perms < ACCESS_EDIT ) {
					$frozen[] = $fullkey;
                }

			}
			$this->page->confessArray($frozen, 
									  'CoopForm::addAndFillVars(fruzen gladje)',
									  3);			
			$this->form->freeze($frozen);
		}

	function findLinkOptions($key)
		{
			$link = explode(':', $this->forwardLinks[$key]);
			$sub =& new CoopObject(&$this->page, $link[0], &$this);

			///$top =& $this->findTop(); // will need this for overrides
            
            $sub->linkConstraints();

            //$this->debugWrap(2);
			$sub->obj->find();

			return array(&$sub, $link);
		}

	function getLinkOptions(&$sub, $link)
		{

            //XXX check to make sure find has been called, error out if not

            // NOTE!!! you must first do the finding outside of here!

			// i ALWAYS want a choose one. always. screw FB.
			$options[] = "-- CHOOSE ONE --";

            // I can't use loaddbresult here. i need concatlinkfields
            //$this->debugWrap(2);
			while($sub->obj->fetch()){
                //$link[1], NOT pk! in custom link, it MIGHT NOT be the pk!
				$options[(string)$sub->obj->$link[1]] = 
					$sub->concatLinkFields();
			}

			$this->page->confessArray($options, 
                                      "CoopForm::getLinkOptions($key)", 6);
			return $options;
		}



	// this is back-called by quickform.
	// remember, you have to  pass QF a callback function. THIS is that func
	function process($vars)
		{
			
			$this->page->confessArray($vars, 
									  sprintf('CoopForm::process(vars): %s', 
											  $this->table), 
									  2);

			$old = $this->obj->__clone($this->obj); // copy, not ref!

            $from =$this->scrubForSave($vars);
			$this->obj->setFrom(&$from);
            confessObj($this->obj, 'CoopForm::process() after being setfrom', 
                       4);

            // NOTE! this is very different from fb. here the OBJ does the
            // work, not the vars
            // ALSO! do it *after* scrubforsave! otherwise it gets wiped out!
            if(is_callable(array($this->obj, 'preProcessForm'))){
                $this->page->printDebug(
                    "CoopForm::process($this->table)) PREPROCESSING", 3);
                // NOTE! i must do this before calling generators
                $this->obj->CoopForm =& $this; 
                call_user_func(array($this->obj, 'preProcessForm'), &$vars);
            }



			// better way to guess?
			if($vars[$this->prependTable($this->pk)]){		
				$this->update($old);
                $type = 'edit';
			} else {
				$this->insert();
                $type = 'add';
			}
			
			$this->processCrossLinks($vars);
			
			$this->enema($vars); // necessary. *sigh*
					
			return sprintf('%s %s was successful!',
                           $this->actionnames[$type], 
                           $this->obj->fb_formHeaderText);
		}


	function scrubForSave($vars)
		{
			$this->_tableDef = $this->obj->table();


			// hack around nulls
			foreach($vars as $fullkey => $val){
				
                // skip non-full-keys
                if(!strstr($fullkey, '-')){
                    continue;
                }

				// XXX is this the right place to strip off the prefix?
				list($table, $key) = explode('-', $fullkey);

				// i only want the ones for this table dude
				if($table != $this->table){
					continue;
				}

				// TODO: check perms
				// i will be duplicating saveok here, basically
								
				//TODO: escape currency chars, as per shared.inc
								
				$this->page->printDebug(
					sprintf("CoopForm::scrubForSave(%s) %d chars<br>", 
						   $fullkey, strlen($val)),3);

				if($val == ''){ 
					$cleanvars[$key] = DB_DataObject_Cast::sql('NULL') ;
				} else if($this->_tableDef[$key] & DB_DATAOBJECT_DATE){
                    if($this->_tableDef[$key] & DB_DATAOBJECT_TIME){
                        $cleanvars[$key] = human_to_sql_timestamp($val);
                    } else {
                        $cleanvars[$key] = human_to_sql_date($val);
                    }
				} else {
					///i don't need to escape here, i don't think
 					$cleanvars[$key] = $val;

				}

			}
            $this->page->confessArray($cleanvars, 
                                      'CoopForm::scrubForSave() cleaned', 4);
			return $cleanvars;

		}

	
	function update(&$old)
		{
			if(!$old->find(true)){
				PEAR::raiseError("save couldn't get its pk. did something else change the record in between editing and saving?", 888);
			}

			if($this->page->debug > 1){
				$this->debugWrap(2);
			}
			if($this->page->debug > 2){
				confessObj($old, 'CoopForm::update() OLD data');
				confessObj($this->obj, 'CoopForm::update() NEW data');
			}

			$this->obj->update($old);
			$this->id = $this->obj->{$this->pk};

            // DO BEFORE SAVING AUDIT! 
            $this->calcChanges(&$old);
		
			$this->saveAudit(false);
		
			return $this->id;
		}


    // populates $this->changes array, which saveaudit needs!
    function calcChanges(&$old)
        {
            foreach($this->obj->table() as $key => $type){
                if($this->obj->$key != $old->$key){
                    // NOTE: i save the whole thing. i do formatting at display
                    // even if it is a massive longtext. it's wasteful, but wtf
                    $this->changes[$key] = array('old' => $old->$key,
                                                 'new' => $this->obj->$key);
                }
            }
        }

	function insert()
		{
			if($this->page->debug > 1){
				$this->debugWrap(2);
			}
			$this->obj->insert();
			$this->id = $this->lastInsertID();
			$this->saveAudit(true);

            //why? because saveaudit above needs lastinsertid. duh.
            if(is_callable(array($this->obj,'afterInsert'))){
                $this->obj->afterInsert(&$this);
            }
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
			
			// TODO set default to , um, default
			
			// selects must stutter: key => val. bah, give me LISP!
			$doubleopt[''] =  '-- CHOOSE ONE --';
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
			if(!empty($this->obj->fb_requiredFields)){

                $this->form->registerRule('customrequired', NULL, 
                                          'CustomRequired', 
                                          'lib/customrequired.php');
                if(!$this->form->isRuleRegistered('customrequired')){
                    PEAR::raiseError('customrequired rule did not register',
                                     666);
                }

				foreach($this->obj->fb_requiredFields as $fieldname){
// 					user_error("CoopForm::addRequiredFields($fieldname)", 
// 							   E_USER_NOTICE);
					$this->form->addRule(
                        $this->prependTable($fieldname), 
                        "{$this->obj->fb_fieldLabels[$fieldname]} mustn't be empty.", 
                        'CustomRequired', NULL, 'client');
                    // NOTE! must do regular required *AND* custom required
                    // customrequired doesn't "qualify" as a required
					$this->form->addRule(
                        $this->prependTable($fieldname), 
                        "{$this->obj->fb_fieldLabels[$fieldname]} mustn't be empty.", 
                        'required');
				}
			}
			//confessObj($this->form, 'ahc');
		}

	
	// XXX i don't like this. i need to set in object, not in form
	// but where? if i'm populating from db, i don't want to clobber with this!
	// maybe do like my fb_ stuff? if it's set, ignore it?
	function setDefaults()
		{
			$this->page->debug > 3 && confessObj($this->page, 
                                                 'setDefaults: coop page');

            // gah. have to prepend table here
            // my two special cases: shcoolyear and family
            if($this->form->elementExists($this->prependTable('school_year'))
               && !$this->form->exportValue($this->prependTable('school_year')))
            {
                $prepended[$this->prependTable('school_year')] = 
                    $this->page->currentSchoolYear;
            }
            
            // DUH! otherwise it won't let a new family go in there.
            if($this->pk != 'family_id' && 
               $this->form->elementExists($this->prependTable('family_id')) &&
               !$this->form->exportValue($this->prependTable('family_id')))
            {
                $prepended[$this->prependTable('family_id')] = 
                    $this->page->userStruct['family_id'];
            }
            
            //NOTE this overrides the above. but defers to submit/set vars
			foreach($this->obj->fb_defaults as $key => $val){
                if($this->form->elementExists($this->prependTable($key)) 
                   && !$this->form->exportValue($this->prependTable($key)))
                {            
                    $prepended[$this->prependTable($key)] = $val;
                }
			}
            $this->page->confessArray($prepended, 
                                      "CoopForm::setDefaults({$this->table}))", 
                                      3);

			$this->form->setDefaults($prepended);
		}

	function processCrossLinks($vars)
		{
			//confessObj($this, 'this');
			print $this->page->confessArray($vars, 
									  'CoopForm::processCrossLinks(vars)', 1);
			
			if(!is_array($this->obj->fb_crossLinks)){
				return;
			}
			//$this->debugWrap(0); 
			// for everything in crosslinks, 
			foreach($this->obj->fb_crossLinks as $la){
				$tf = $la['toField'];
				$longtf = $this->prependTable($tf);
				$mt = $la['table'];
				$ft = $la['toTable'];
				$nk = $this->backlinks[$mt];
				if(!($tf && $mt && $ft && $nk)){
					PEAR::raiseError('your crosslinks spec sucks', 777);
				}
				
 				if(!isset($vars[$longtf])){
					// XXX this scares me. if i forget to include these...
					// then they get wiped out of the db? that seems wrong to me.
					$vars[$longtf] = array();
					//continue;  // XXX WTF? why was i continuing!!? debug?
 				}

				//print "mt $mt tf $tf ft $ft nk $nk";
				
				// yeah, array_diff is the long way with db thrashing.
				// but i want clear, easily-debugged code
				$this->obj->{$this->pk} = $this->id;
				$indb = $this->checkCrossLinks($mt,$ft);
				$this->page->confessArray($indb, 
										  'CoopForm::processCrossLinks(indb)');
				$toSave = array_diff($vars[$longtf], $indb);
				if(count($vars[$longtf]) < count($indb)){
					$toDelete = array_diff($indb, $vars[$longtf]);
				}
				$this->page->confessArray($toSave, 
										  'CoopForm::processsCrossLinks(save)', 
										  2);
				$this->page->confessArray($toDelete, 
										  'CoopForm::processsCrossLinks(delete)',
										  2);


				//$this->debugWrap(2);
				// save
				if(is_array($toSave)){
					foreach($toSave as $saveme){
						$mid =& new CoopObject(&$this->page, $mt, &$this);
						$mid->obj->$tf = $saveme;
						$mid->obj->$nk = $this->id;
						// TODO: handle schoolyear/other vars here?
						$mid->obj->insert();
					}
				}
				
				// delete
				if(is_array($toDelete)){
					foreach($toDelete as $killme){
						$mid =& new CoopObject(&$this->page, $mt, &$this);
						$mid->obj->$tf = $killme;
						$mid->obj->$nk = $this->id;
						// TODO: handle schoolyear/other vars here?
						$mid->obj->limit(1);
						$mid->obj->delete();
					}
				}

			}
		}

    // now, remember. these are totally different tables that
    // COOPFORM AND THIS DBDO DOESN"T KNOW ABOUT! treat it carefully
	function addCrossLinks($vars)
		{
			
			if(!is_array($this->obj->fb_crossLinks)){
				return;
			}

			foreach($this->obj->fb_crossLinks as $la){
				$tf = $la['toField'];
				$longtf = $this->prependTable($tf);
				$mt = $la['table'];
				$ft = $la['toTable'];
				$nk = $this->backlinks[$mt];


				$this->page->printDebug(
                    "processing crosslinks $mt:$nk $ft:$tf ",
                                        2);

                // links will add or delete from the mid table!
				$mid = new CoopObject(&$this->page, $mt, $this);
                if($mid->isPermittedField(null, !$this->id, 
                                          !$this->id) < ACCESS_DELETE)
                {
                    return;
                }

				//duplication of selectoptions
                $this->debugWrap(5);
				$far = new CoopObject(&$this->page, $ft, $this);

                // i obviously need to view the remotes
                if($far->isPermittedField(null, !$this->id, 
                                          !$this->id) < ACCESS_VIEW){
                    return;
                }


				// IIRC this has a different name in FB. use theirs!
                $this->linkConstraints();

				$far->obj->orderBy(implode(', ', 
										   $far->obj->fb_linkDisplayFields));
				$far->obj->find();
				while($far->obj->fetch()){
					$options[(string)$far->obj->$tf] = 
							 sprintf('%.42s...', 
									 $far->concatLinkFields());
				}
				
	
				$this->obj->{$this->pk} = $this->id; // for checkcrosslinks
				if(isset($vars[$longtf])){
					$incl = $vars[$longtf];
				} else {
					$incl = $this->checkCrossLinks($mt, $ft);
				}
				$this->page->confessArray($incl, 
										  'CoopForm::addCrossLinks(incl)', 2);

				// HACK! MUST sanity czech that indb is in options, and push
				$optkeys = array_keys($options);
				foreach($incl as $key ){
					if(!in_array($key, $optkeys)){
						$far = new CoopObject(&$this->page, $ft, &$this);
                        //TODO: try instead $far->obj->get($tf, $key)
						$far->obj->$tf = $key;
						$far->obj->find(true);
						$options[$key] = 
							 sprintf('%.42s...', 
									 $far->concatLinkFields());
					}
				}
				
				// jeebus. all this, just to get to here.
				$el =& $this->form->addElement('advmultselect', 
										$longtf,
										$far->title(),
										$options);
                // XXX THIS IS TOTALLY BROKEN IN MY NEW last[] thing!
 				$el->setValue($this->isSubmitted() ? $vars[$tf] : $incl);
				
			}
		}

    /// XXXXX have to use REQUEST here!
    /// not getsubmitvalues because it doesn't show __qf!
    /// i suspect Trouble with this approach
	function isSubmitted()
		{
            $sv= $_REQUEST;
            $submitted = isset($sv['_qf__' . $this->form->_attributes['name']]);
            $this->page->confessArray($sv, 
                                      "CoopForm::isSubmitted(): [$submitted]", 
                                      2);
            return $submitted; 
		}

	function legacyPassThru()
		{
			// ugly assthrus for my old-style dispatcher
			// XXX these conflict with the new dispatcher!
			$this->form->addElement('hidden', $this->prependTable($this->pk), 
									$this->obj->{$this->pk}); 

		}
	
	function dupeCheck($vars)
		{
			if($vars[$this->prependTable($this->pk)] > 0){
				return true;	// i'm editing. no dupecheck on edit.
			}

            // i don't want to even think about dupechecking
            // if there are other errors, it most CERTAINLY is not a dupe!
            if(count($this->form->_errors)){
                return true;
            }


			//TODO check for a dupecheck function in the object, and use that
			// then revert to this as a default
			// also check for a list of dupeignore fields. hmm. which easier?
			$temp =& new CoopObject(&$this->page, $this->table, &$this);
			$foo = $temp->obj;
			$foo->whereAdd();
			$this->debugWrap(4);
			$ov = $foo->keys();
            // let scrubforsave do the work, instead of duplicatinghere
            foreach($this->scrubForSave($vars) as $key => $val){
                if(empty($this->obj->fb_dupeIgnore) ||
                       !in_array($key, $this->obj->fb_dupeIgnore))
                {
                    $scrubbed[$key] = $val;
                }
            }
            $this->page->confessArray($scrubbed, 'scrubby', 2);
            if(count($scrubbed) < 1){
                $this->page->printDebug('SERIOUS ERROR IN DUPECHECK! trying to search for NOTHING!');
                
                confessObj($foo, "{$this->table} dupes found ");
            }
            $foo->setFrom($scrubbed);
			if($foo->find(true)){
				foreach($vars as $fullkey => $val){
					list($table, $key) = explode('-', $fullkey);
					if($table != $this->table){
						continue;
					}
                    //XXX why is this != instead of == !!?? makes no SENSE!!!
					if($scrubbed[$key] != $foo->$key){
						$duples[$fullkey] = 'Duplicate entry.';
					}
				}
                $this->page->debug > 1 && 
                    confessObj($foo, "{$this->table} dupes found ");
                $duples[NULL] = "Duplicate entry (see below).";
				return $duples ;
			}
            // TODO:: you need to add a duplicate entry FORM error!
			return true;
		}
			

	// cleans out REQUEST after a save! vital for re-displaying new.
	function enema($vars)
		{
            //brutally squash all submitvars
            $this->page->vars['last']['submitvars'] = array();

            // now more daintily wipe out the REQUESTS
			//$ov = $this->obj->keys();
			$ov = array_keys(get_object_vars($this->obj));
 			foreach($ov as $key){
				$fullkey = $this->prependTable($key);
                $this->page->printDebug("$this->table unsetting $fullkey",
                                        3);
 				unset($_REQUEST[$fullkey]);
 			}
			// i have to hack qf tracksumbit here.
			// otherwise it puts old data back!
			unset($_REQUEST['_qf__' . $this->form->_attributes['name']]);
			
			// now the xlinks too
			if(!is_array($this->obj->fb_crossLinks)){
				return;
			}
            // don't i need to purge the xlinks too?
			foreach($this->obj->fb_crossLinks as $la){
				unset($_REQUEST[$this->prependTable($la['toField'])]);
			}
		}

	// prepends the name fo this table to a field, returns the new long name
	function prependTable($col)
		{
			return sprintf('%s-%s', $this->table , $col);
		}

    // wrapper so that i can see when it doesn't validate. this is silly
	function validate()
		{
            $this->page->confessArray($this->form->_rules, 
                                      'validate  rules', 4);
            $this->page->confessArray($this->form->_formRules, 
                                      'validate  formrules', 5);
            $this->page->confessArray($this->form->_required, 
                                      'validate  required fields', 5);
  
            $this->page->confessArray($this->form->getRegisteredRules(), 
                                      'validate  registeredrules', 4);
            
            
			$temp  = $this->form->validate(); // FORM!
			if($temp == false){
                $this->page->confessArray(
                    $this->form->_errors, 
                    "CoopForm::validate({$this->table}) didn't validate", 3);
			}
            


			return  $temp;
		}


	//FB API compatibility
	function useForm(&$form)
		{
			$this->form =& $form;
		}

	

} // END COOP FORM CLASS

////KEEP EVERTHANG BELOW

?>