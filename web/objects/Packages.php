<?php
/**
 * Table Definition for packages
 */
require_once 'DB/DataObject.php';

class Packages extends DB_DataObject 
{
	var $textFields = array ('package_description');

    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'packages';                        // table name
    var $package_id;                      // int(32)  not_null primary_key unique_key auto_increment
    var $package_type;                    // string(8)  enum
    var $package_number;                  // string(20)  
    var $package_title;                   // string(255)  
    var $package_description;             // blob(16777215)  blob
    var $package_value;                   // real(11)  
    var $item_type;                       // string(16)  enum
    var $donated_by_text;                 // string(255)  
    var $starting_bid;                    // real(11)  
    var $bid_increment;                   // real(11)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Packages',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

}
