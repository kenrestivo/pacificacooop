<?php
/**
 * Table Definition for auction_purchases
 */
require_once 'DB/DataObject.php';

class Auction_purchases extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'auction_purchases';               // table name
    var $auction_purchase_id;             // int(32)  not_null primary_key unique_key auto_increment
    var $springfest_attendee_id;          // int(32)  
    var $package_id;                      // int(32)  
    var $package_sale_price;              // real(11)  
    var $income_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Auction_purchases',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('package_id', 'package_sale_price', 
									  'springfest_attendee_id');
	var $fb_fieldLabels = array (
		'springfest_attendee_id' => 'Springfest Attendee',
		'package_id' => "Auction Package Purchased",
		'package_sale_price' => 'Final Bid Price',
		'income_id' => "Payment Information"
		);
	var $fb_fieldsToRender = array (
		'package_id',
//		'income_id',
		'package_sale_price'
		);
	var $fb_formHeaderText =  'Springfest Auction Purchases';


}
