<?php
/**
 * Table Definition for invitations
 */
require_once 'DB/DataObject.php';

class Invitations extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'invitations';                     // table name
    var $invitation_id;                   // int(32)  not_null primary_key unique_key auto_increment
    var $lead_id;                         // int(32)  not_null
    var $school_year;                     // string(50)  
    var $family_id;                       // int(32)  
    var $relation;                        // string(8)  enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Invitations',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('relation');
	
	function preGenerateForm()
		{
	// 		$this->fb_preDefElements['school_year'] = 
// 			HTML_QuickForm::createElement(
// 				'select', 
// 				'school_year', $this->getFieldLabel('school_year'), 
// 				$this->getSelectOptions('school_year'), 
// 				null);
			
		}

	var $fb_selectAddEmpty = array ('family_id', 'lead_id');}
