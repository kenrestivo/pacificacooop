<?php
/**
 * Table Definition for events
 */
require_once 'DB/DataObject.php';

class Events extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'events';                          // table name
    var $event_id;                        // int(32)  not_null primary_key unique_key auto_increment
    var $description;                     // string(255)  
    var $entered;                         // datetime(19)  
    var $updated;                         // timestamp(14)  not_null unsigned zerofill timestamp
    var $audit_user_id;                   // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Events',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
