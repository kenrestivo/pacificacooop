<?php
/**
 * Table Definition for sponsorships
 */
require_once 'DB/DataObject.php';

class Sponsorships extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'sponsorships';                    // table name
    var $sponsorship_id;                  // int(32)  not_null primary_key unique_key auto_increment
    var $company_id;                      // int(32)  
    var $lead_id;                         // int(32)  
    var $sponsorship_type_id;             // int(32)  
    var $entry_type;                      // string(9)  enum
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sponsorships',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
