<?php
/**
 * Table Definition for invitation_rsvps
 */
require_once 'DB/DataObject.php';

class Leads_income_join extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'leads_income_join';               // table name
    var $leads_income_join_id;            // int(32)  not_null primary_key unique_key auto_increment
    var $income_id;                       // int(32)  
    var $lead_id;                         // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Leads_income_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_fieldLabels = array(
        'income_id' => "Payment Information",
		'lead_id' => 'Invitee',
		);
	var $fb_fieldsToRender = array ('lead_id', 'income_id');
	var $fb_formHeaderText = "Springfest Donations from Invitations";

	var $fb_linkDisplayFields = array('lead_id', 'income_id');
}
