<?php
/**
 * Table Definition for auction_donation_items
 */
require_once 'DB/DataObject.php';

class Auction_donation_items extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'auction_donation_items';          // table name
    var $auction_donation_item_id;        // int(32)  not_null primary_key unique_key auto_increment
    var $item_description;                // blob(16777215)  blob
    var $item_value;                      // real(11)  
    var $entered;                         // datetime(19)  binary
    var $updated;                         // timestamp(19)  not_null unsigned zerofill binary timestamp
    var $date_received;                   // date(10)  binary
    var $audit_user_id;                   // int(32)  
    var $location_in_garage;              // string(255)  
    var $quantity;                        // int(5)  
    var $item_type;                       // string(16)  enum
    var $school_year;                     // string(50)  
    var $committed;                       // string(3)  enum
    var $thank_you_id;                    // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Auction_donation_items',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('auction_donation_item_id', 
									  'item_description');
	var $fb_selectAddEmpty = array ('package_id');
	var $fb_fieldLabels = array ('item_description' => 'Item Description');
	var $fb_enumFields = array ('item_type');
	var $fb_textFields = array ('item_description'); 
	var $fb_fieldsToRender = array('item_description', 'item_value',  
								   'quantity', 'school_year', 'thank_you_id');
	var $fb_fieldLabels = array(
		"family_id" => "Co-Op Family", //  XXX this is bunk. 
		"quantity" => "Quantity of items", 
		"item_description" => "Description of item" ,
		'item_value' => 'Estimated TOTAL Value ($)' ,
		"item_type" => "Physical Product or Gift Certificate",
		"date_received" => "Date Item received" ,
		"location_in_garage" => "Where It's Located" ,
		"school_year" => "School Year" ,
		"auction_donation_item_id" => "Unique ID" ,
		"thank_you_id" => "Thank-You Sent" 
		);
	var $fb_formHeaderText =  'Springfest Auction Donation Items';
	var $fb_crossLinks = array(array('table' => 'auction_packages_join', 
									 'toTable' => 'packages',
									 'toField' => 'package_id',
									 'type' => 'select'));

	var $fb_requiredFields = array('item_description', 'quantity', 
								   'school_year',  'item_value', 
								   'item_type');

	function fb_linkConstraints()
		{
			$this->school_year = findSchoolYear(); 
			$this->whereAdd("date_received > '2000-01-01'");
		}

}
