<?php
/**
 * Table Definition for leads
 */
require_once 'DB/DataObject.php';

class Leads extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'leads';                           // table name
    var $lead_id;                         // int(32)  not_null primary_key unique_key auto_increment
    var $last_name;                       // string(255)  
    var $first_name;                      // string(255)  
    var $salutation;                      // string(50)  
    var $title;                           // string(255)  
    var $company;                         // string(255)  
    var $address1;                        // string(255)  
    var $address2;                        // string(255)  
    var $city;                            // string(255)  
    var $state;                           // string(255)  
    var $zip;                             // string(255)  
    var $country;                         // string(255)  
    var $phone;                           // string(255)  
    var $relation;                        // string(8)  enum
    var $source;                          // string(10)  enum
    var $family_id;                       // int(32)  
    var $do_not_contact;                  // date(10)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Leads',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
