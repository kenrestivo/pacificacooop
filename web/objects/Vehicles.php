<?php
/**
 * Table Definition for vehicles
 */
require_once 'DB/DataObject.php';

class Vehicles extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'vehicles';                        // table name
    var $vid_number;                      // string(17)  not_null primary_key
    var $insurance_information_id;        // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Vehicles',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
