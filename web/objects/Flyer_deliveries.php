<?php
/**
 * Table Definition for flyer_deliveries
 */
require_once 'DB/DataObject.php';

class Flyer_deliveries extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'flyer_deliveries';                // table name
    var $flyer_delivery_id;               // int(32)  not_null primary_key unique_key auto_increment
    var $flyer_type;                      // string(255)  
    var $delivered_date;                  // date(10)  
    var $family_id;                       // int(32)  
    var $company_id;                      // int(32)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Flyer_deliveries',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
