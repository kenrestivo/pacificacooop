<?php
/**
 * Table Definition for in_kind_donations
 */
require_once 'DB/DataObject.php';

class In_kind_donations extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'in_kind_donations';               // table name
    var $in_kind_donation_id;             // int(32)  not_null primary_key unique_key auto_increment
    var $item_description;                // blob(16777215)  blob
    var $quantity;                        // int(5)  
    var $item_value;                      // real(11)  
    var $date_received;                   // date(10)  binary
    var $school_year;                     // string(50)  
    var $thank_you_id;                    // int(32)  
    var $_cache_item_description;         // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('In_kind_donations',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_formHeaderText = 'Springfest In-Kind Donations';
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


var $fb_shortHeader = 'In-kind Donations';

var $fb_requiredFields = array(
   'quantity',
   'item_description',
   'item_value',
   'family_id',
   'school_year'
);

var $fb_defaults = array(
  'quantity' => 1
);

var $fb_currencyFields = array(
   'item_value'
);


   var $fb_sizes = array(
     'item_description' => 100
   );

// set item_description lines = 3



}
