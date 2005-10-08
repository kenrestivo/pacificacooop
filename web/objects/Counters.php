<?php
/**
 * Table Definition for counters
 */
require_once 'DB/DataObject.php';

class Counters extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'counters';                        // table name
    var $counter_id;                      // int(32)  not_null primary_key unique_key auto_increment
    var $column_name;                     // string(255)  
    var $counter;                         // int(10)  unsigned
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Counters',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
