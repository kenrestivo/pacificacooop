<?php
/**
 * Table Definition for sponsorship_types
 */
require_once 'DB/DataObject.php';

class Sponsorship_types extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'sponsorship_types';               // table name
    var $sponsorship_type_id;             // int(32)  not_null primary_key unique_key auto_increment
    var $sponsorship_name;                // string(50)  not_null
    var $sponsorship_description;         // string(255)  
    var $sponsorship_price;               // real(11)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sponsorship_types',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
