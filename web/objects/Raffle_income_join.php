<?php
/**
 * Table Definition for raffle_income_join
 */
require_once 'DB/DataObject.php';

class Raffle_income_join extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'raffle_income_join';              // table name
    var $raffle_income_join_id;           // int(32)  not_null primary_key unique_key auto_increment
    var $raffle_location_id;              // int(32)  
    var $income_id;                       // int(32)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Raffle_income_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
