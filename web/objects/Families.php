<?php
/**
 * Table Definition for families
 */
require_once 'DB/DataObject.php';

class Families extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'families';                        // table name
    var $family_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $name;                            // string(255)  
    var $phone;                           // string(20)  
    var $address;                         // string(255)  
    var $email;                           // string(255)  
    var $address1;                        // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Families',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $select_display_field = 'name';
	var $fieldLabels = array ('name' => 'Family Name');
}
