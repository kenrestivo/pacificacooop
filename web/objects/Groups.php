<?php
/**
 * Table Definition for groups
 */
require_once 'DB/DataObject.php';

class Groups extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'groups';                          // table name
    var $group_id;                        // int(32)  not_null primary_key unique_key auto_increment
    var $name;                            // string(55)  
    var $audit_user_id;                   // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Groups',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
