<?php
/**
 * Table Definition for companies_in_kind_join
 */
require_once 'DB/DataObject.php';

class Companies_in_kind_join extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'companies_in_kind_join';          // table name
    var $companies_in_kind_join_id;       // int(32)  not_null primary_key unique_key auto_increment
    var $in_kind_donation_id;             // int(32)  
    var $company_id;                      // int(32)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Companies_in_kind_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
