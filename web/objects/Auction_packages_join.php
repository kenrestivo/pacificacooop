<?php
/**
 * Table Definition for auction_packages_join
 */
require_once 'DB/DataObject.php';

class Auction_packages_join extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'auction_packages_join';           // table name
    var $auction_packages_join_id;        // int(32)  not_null primary_key unique_key auto_increment
    var $package_id;                      // int(32)  
    var $auction_donation_item_id;        // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Auction_packages_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
