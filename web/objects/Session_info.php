<?php
/**
 * Table Definition for session_info
 */
require_once 'DB/DataObject.php';

class Session_info extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'session_info';                    // table name
    var $session_id;                      // string(32)  not_null primary_key unique_key
    var $ip_addr;                         // string(20)  
    var $entered;                         // datetime(19)  
    var $updated;                         // timestamp(14)  not_null unsigned zerofill timestamp
    var $user_id;                         // int(32)  
    var $vars;                            // blob(65535)  blob binary

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Session_info',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
