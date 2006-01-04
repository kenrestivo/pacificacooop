<?php
/**
 * Table Definition for minutes
 */
require_once 'CoopDBDO.php';

class Minutes extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'minutes';                         // table name
    var $minutes_id;                      // int(32)  not_null primary_key unique_key auto_increment
    var $calendar_event_id;               // int(32)  
    var $body;                            // blob(16777215)  blob

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Minutes',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('calendar_event_id', 'body');

    var $fb_textFields = array('body');

	var $fb_fieldLabels = array(
        'calendar_event_id' => 'Meeting',
        'body' => 'Minutes'
		);

	var $fb_formHeaderText =  'Meeting Minutes';

	var $fb_requiredFields = array('calendar_event_id', 'body');


    var $fb_shortHeader = 'Minutes';

    var $fb_joinPaths = array('school_year' => 'calendar_events');


// 	function fb_linkConstraints(&$co)
// 		{
//             $cal =& new CoopObject(&$co->page, 'calendar_events', &$co);
//             $co->protectedJoin($cal);
//             $co->constrainSchoolYear();
//             $co->orderByLinkDisplay();
//             $co->grouper();
// 		}



}
