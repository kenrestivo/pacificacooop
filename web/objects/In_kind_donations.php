<?php
/**
 * Table Definition for in_kind_donations
 */
require_once 'DB/DataObject.php';

class In_kind_donations extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'in_kind_donations';               // table name
    var $in_kind_donation_id;             // int(32)  not_null primary_key unique_key auto_increment
    var $item_description;                // blob(16777215)  blob
    var $quantity;                        // int(5)  
    var $item_value;                      // real(11)  
    var $date_received;                   // date(10)  
    var $school_year;                     // string(50)  
    var $thank_you_id;                    // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('In_kind_donations',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $kr_longTitle = 'Springfest In-Kind Donations';
	var $fb_fieldLabels = array(
		"family_id" => "Co-Op Family",
		"quantity" => "Quantity of items", 
		"item_description" => "Description of item" ,
		'item_value' => 'Estimated TOTAL Value ($)' ,
		"date_received" => "Date Item received" ,
		"school_year" => "School Year" ,
		"in_kind_donation_id" => "Unique ID" ,
		"thank_you_id" => "Thank-You Sent" 
		);
	var $fb_linkDisplayFields = array('item_description');

}
