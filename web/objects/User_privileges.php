<?php
/**
 * Table Definition for user_privileges
 */
require_once 'DB/DataObject.php';

class User_privileges extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'user_privileges';                 // table name
    var $privilege_id;                    // int(32)  not_null primary_key unique_key auto_increment
    var $user_id;                         // int(32)  
    var $group_id;                        // int(32)  
    var $realm;                           // string(55)  
    var $user_level;                      // int(5)  
    var $group_level;                     // int(5)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('User_privileges',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
