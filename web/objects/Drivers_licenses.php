<?php
/**
 * Table Definition for drivers_licenses
 */
require_once 'DB/DataObject.php';

class Drivers_licenses extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'drivers_licenses';                // table name
    var $drivers_license_id;              // int(32)  not_null primary_key unique_key auto_increment
    var $last_name;                       // string(255)  
    var $first_name;                      // string(255)  
    var $middle_name;                     // string(255)  
    var $state;                           // string(40)  
    var $license_number;                  // string(100)  
    var $expiration_date;                 // date(10)  
    var $parent_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Drivers_licenses',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
