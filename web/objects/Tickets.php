<?php
/**
 * Table Definition for invitation_rsvps
 */
require_once 'DB/DataObject.php';

class Tickets extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'tickets';                         // table name
    var $ticket_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $income_id;                       // int(32)  
    var $ticket_quantity;                 // int(5)  
    var $lead_id;                         // int(32)  
    var $school_year;                     // string(50)  
    var $ticket_type;                     // string(21)  enum
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Tickets',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_fieldLabels = array(
        'income_id' => "Payment Information",
		'ticket_quantity' => 'Number of tickets',
		'lead_id' => 'Invitee- from Invitations',
		'company_id' => 'Invitee- from Solicitation',
		'family_id' => 'Family- tickets for members',
		'school_year' => 'School Year',
		'ticket_type' => 'Type of Ticket'
		);
	var $fb_fieldsToRender = array ('ticket_quantity',
									'school_year', 'ticket_type', 'income_id'
		);
	var $fb_formHeaderText = "Springfest Event Tickets";

	var $fb_linkDisplayFields = array('lead_id', 'income_id');
}
