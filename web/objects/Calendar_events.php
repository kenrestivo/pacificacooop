<?php
/**
 * Table Definition for calendar_events
 */
require_once 'DB/DataObject.php';

class Calendar_events extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'calendar_events';                 // table name
    var $calendar_event_id;               // int(32)  not_null primary_key unique_key auto_increment
    var $event_id;                        // int(32)  
    var $notes;                           // blob(16777215)  blob
    var $status;                          // string(9)  enum
    var $keep_event_hidden_until_date;    // datetime(19)  
    var $event_date;                      // datetime(19)  
    var $url;                             // string(255)  
    var $entered;                         // datetime(19)  
    var $updated;                         // timestamp(14)  not_null unsigned zerofill timestamp
    var $audit_user_id;                   // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Calendar_events',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('status');

}
