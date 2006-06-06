<?php
/**
 * Table Definition for territories_families_join
 */
require_once 'COOP/DBDO.php';

class Territories_families_join extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'territories_families_join';       // table name
    var $territories_families_id;         // int(32)  not_null primary_key unique_key auto_increment
    var $territory_id;                    // int(32)  
    var $family_id;                       // int(32)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Territories_families_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
