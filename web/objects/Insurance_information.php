<?php
/**
 * Table Definition for insurance_information
 */
require_once 'DB/DataObject.php';

class Insurance_information extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'insurance_information';           // table name
    var $insurance_information_id;        // int(32)  not_null primary_key unique_key auto_increment
    var $last_name;                       // string(255)  
    var $first_name;                      // string(255)  
    var $middle_name;                     // string(255)  
    var $policy_number;                   // string(255)  
    var $policy_expiration_date;          // date(10)  binary
    var $companyname;                     // string(255)  
    var $naic;                            // int(5)  
    var $parent_id;                       // int(32)  
    var $audit_user_id;                   // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Insurance_information',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
