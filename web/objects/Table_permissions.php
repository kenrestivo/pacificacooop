<?php
/**
 * Table Definition for table_permissions
 */
require_once 'DB/DataObject.php';

class Table_permissions extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'table_permissions';               // table name
    var $table_permissions_id;            // int(32)  not_null primary_key unique_key auto_increment
    var $table_name;                      // string(255)  
    var $field_name;                      // string(255)  
    var $group_id;                        // int(32)  
    var $realm_id;                        // int(32)  
    var $user_level;                      // int(5)  
    var $group_level;                     // int(5)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Table_permissions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
