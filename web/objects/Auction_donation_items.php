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
    var $entered;                         // datetime(19)  
    var $updated;                         // timestamp(14)  not_null unsigned zerofill timestamp
    var $date_received;                   // date(10)  
    var $audit_user_id;                   // int(32)  
    var $location_in_garage;              // string(255)  
    var $quantity;                        // int(5)  
    var $package_id;                      // int(32)  
    var $item_type;                       // string(16)  enum
    var $school_year;                     // string(50)  
    var $committed;                       // string(3)  enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Auction_donation_items',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('item_description');
	var $fb_selectAddEmpty = array ('package_id');
	var $fb_fieldLabels = array ('item_description' => 'Item Description');
	var $fb_enumFields = array ('item_type');

}
