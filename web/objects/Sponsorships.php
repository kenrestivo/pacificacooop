<?php
/**
 * Table Definition for sponsorships
 */
require_once 'DB/DataObject.php';

class Sponsorships extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'sponsorships';                    // table name
    var $sponsorship_id;                  // int(32)  not_null primary_key unique_key auto_increment
    var $company_id;                      // int(32)  
    var $lead_id;                         // int(32)  
    var $sponsorship_type_id;             // int(32)  
    var $entry_type;                      // string(9)  enum
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sponsorships',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('entry_type');
	var $fb_formHeaderText =  'Springfest Sponsorships';
    var $fb_shortHeader = 'Sponsorships';
	var $fb_linkDisplayFields = array('company_id', 'lead_id',
									  'school_year');
	var $fb_fieldLabels = array (
		'company_id' => 'Company Name',
		'lead_id' => 'Invitee Name',
		'sponsorship_type_id' => 'Sponsorship Package',
		'entry_type' => 'Entry Control',
		'school_year' => 'School Year'
		);
	
	var $fb_fieldsToRender = array (
		'company_id' ,
		'lead_id' ,
		'sponsorship_type_id', 
		'entry_type',
		'school_year' 
		);
	var $fb_requiredFields = array (
		'sponsorship_type_id', 
		'entry_type',
		'school_year' 
		);




    function _onlyOne($vars)
        {
            // AHA! need to prependtable!
            // XXX need to get a coopobject in here somehow
            if($vars['sponsorships-lead_id'] > 0 && $vars['sponsorships-company_id'] > 0){
                $msg = "You can have an Invitee Name, or a Company Name, but not both.";    $err['sponsorships-lead_id'] = $msg;
                $err['sponsorships-company_id'] = $msg;
                return $err;
            }
            
            if($vars['sponsorships-lead_id'] <1 && $vars['sponsorships-company_id'] <1){
                $msg = "You must have either an Invitee Name, or a Company Name.";
                $err['sponsorships-lead_id'] = $msg;
                $err['sponsorships-company_id'] = $msg;
                return $err;
            }
            
            return true; 				// copacetic
        }
    
    function postGenerateForm(&$form)
        {
            $form->addFormRule(array($this, '_onlyOne'));
            $el =& $form->getElement($form->CoopForm->prependTable('entry_type'));
            $el->setValue('Manual');

        }


}
