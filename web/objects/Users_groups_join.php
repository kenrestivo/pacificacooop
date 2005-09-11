<?php
/**
 * Table Definition for users_groups_join
 */
require_once 'DB/DataObject.php';

class Users_groups_join extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'users_groups_join';               // table name
    var $users_groups_join_id;            // int(32)  not_null primary_key unique_key auto_increment
    var $user_id;                         // int(32)  
    var $group_id;                        // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Users_groups_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


}
