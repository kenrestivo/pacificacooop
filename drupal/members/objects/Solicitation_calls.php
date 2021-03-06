<?php
/**
 * Table Definition for solicitation_calls
 */
require_once 'DB/DataObject.php';

class Solicitation_calls extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'solicitation_calls';              // table name
    var $solicitation_call_id;            // int(32)  not_null primary_key unique_key auto_increment
    var $method_of_contact;               // string(8)  enum
    var $company_id;                      // int(32)  
    var $call_note;                       // blob(16777215)  blob
    var $family_id;                       // int(32)  
    var $done;                            // date(10)  binary
    var $school_year;                     // string(50)  
    var $_cache_call_note;                // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Solicitation_calls',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_fieldLabels = array(
		"company_id" => "Company Name",
		"method_of_contact" => "Contact Method",
		"call_note" => "Note" ,
		"family_id" => "Soliciting Family",
		"done" => "Date of Call" ,
		"school_year" => "School Year" 
		);
	var $fb_formHeaderText = 'Springfest Solicitation Miscellaneous Notes';

    var $fb_shortHeader = 'Misc. Notes';

    var $fb_requiredFields = array(
        'company_id',
        'method_of_contact',
        'family_id',
        'done',
        'school_year'
        );

   var $fb_sizes = array(
     'call_note' => 100
   );

   var $fb_enumFields = array(
     'method_of_contact'
   );


// set call_note lines = 3
}
