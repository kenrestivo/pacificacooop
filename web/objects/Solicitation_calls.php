<?php
/**
 * Table Definition for solicitation_calls
 */
require_once 'DB/DataObject.php';

class Solicitation_calls extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'solicitation_calls';              // table name
    var $solicitation_call_id;            // int(32)  not_null primary_key unique_key auto_increment
    var $method_of_contact;               // string(8)  enum
    var $company_id;                      // int(32)  
    var $call_note;                       // blob(16777215)  blob
    var $family_id;                       // int(32)  
    var $done;                            // datetime(19)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Solicitation_calls',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_fieldsToRender = array ('method_of_contact', 'call_note', 
									'family_id', 'done' );

	var $fb_fieldLabels = array(
		"company_id" => "Company Name",
		"method_of_contact" => "Contact Method",
		"call_note" => "Note" ,
		"family_id" => "Soliciting Family",
		"done" => "Date of Call (MM/DD/YYYY)" ,
		"school_year" => "School Year (YYYY-YYYY)" 
		);
	var $kr_longTitle = 'Springfest Solicitation Miscellaneous Notes';
}
