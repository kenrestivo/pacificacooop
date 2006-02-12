<?php
/**
 * Table Definition for package_types
 */
require_once 'CoopDBDO.php';

class Package_types extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'package_types';                   // table name
    var $package_type_id;                 // int(32)  not_null primary_key unique_key auto_increment
    var $package_type_short;              // string(50)  
    var $sort_order;                      // int(3)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Package_types',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
