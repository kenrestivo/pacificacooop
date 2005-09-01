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
    var $status;                          // string(9)  enum
    var $keep_event_hidden_until_date;    // datetime(19)  binary
    var $event_date;                      // datetime(19)  binary
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Calendar_events',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('status');

	var $fb_linkDisplayFields = array('event_id', 'event_date');
    var $fb_fieldLabels = array(
        'status' => 'Status',
        'event_id' => 'Event',
        'event_date' => 'Date',
        'keep_event_hidden_until_date' => 'Do not show until',
        'school_year' => 'School Year'
        );
	var $fb_formHeaderText =  'Calendar of Events';
	var $fb_shortHeader =  'Calendar';


}
