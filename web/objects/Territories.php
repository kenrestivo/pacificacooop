<?php
/**
 * Table Definition for territories
 */
require_once 'DB/DataObject.php';

class Territories extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'territories';                     // table name
    var $territory_id;                    // int(32)  not_null primary_key unique_key auto_increment
    var $description;                     // string(255)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Territories',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
