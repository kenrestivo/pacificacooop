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
        'event_date' => 'Date of Event',
        'keep_event_hidden_until_date' => 'Hide this Event Until',
        'school_year' => 'School Year'
        );
    var $fb_fieldsToRender = array(
        'status' ,
        'event_id',
        'event_date', 
        'school_year' 
        );
	var $fb_formHeaderText =  'Calendar of Events';
	var $fb_shortHeader =  'Calendar';

    function fb_display_view()
        {
            $this->orderBy('event_date asc');
            return $this->CoopView->simpleTable();
        }

    function fb_display_summary($publiconly = false)
        {
            if($this->CoopView->page->auth['token'] && !$publiconly){
                $clause = 'members'; 
            } else {
                $clause = 'public'; 
            }
            //TODO: shouldn't it automatically join all subthings?
            //isnt' that in dbdo? getlinks?
            $this->whereAdd('show_on_public_page = "Yes"');

            $this->whereAdd(sprintf('event_date >= "%s"', date('Y-m-d')));
            $this->limit(4);
            while($this->fetch()){
                $res .= sprintf("<p>%s:&nbsp;<b>%s</b></p><p>%s</p><br>", 
                                $this->human_date, $this->description, 
                                $this->notes,
                                $publiconly ? '' : 
                                $this->CoopView->recordButtons(&$this, false)
                    );
            }
            return $res;

        }

}
