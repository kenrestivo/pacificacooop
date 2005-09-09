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
    var $show_on_public_page;             // string(7)  enum

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
        'event_date' => 'Date (and time) of Event (MM/DD/YYYY), with optional "HH:MM AM/PM"',
        'keep_event_hidden_until_date' => 'Hide this Event Until',
        'school_year' => 'School Year',
        'show_on_public_page' => 'Show on publicly-accessible home page?'
        );
	var $fb_formHeaderText =  'Calendar of Events';
	var $fb_shortHeader =  'Calendar';
    var $fb_fieldsToUnRender = array('keep_event_hidden_until_date');
    var $fb_enumFields = array('show_on_public_page');

    function fb_display_view(&$co)
        {
            $this->orderBy('event_date asc');
            return $co->simpleTable();
        }

    function fb_display_summary(&$co, $publiconly = false)
        {
            
            $ev =& $this->factory('events');
            $this->joinAdd($ev);

            if(!$co->page->auth['token'] || $publiconly){
                $clause = 'public'; 
                $this->show_on_public_page = 'Yes';            
            }
            $this->status = 'Active';

            $this->whereAdd(sprintf('event_date >= "%s"', date('Y-m-d')));
            $this->whereAdd(
                sprintf(
                    '(keep_event_hidden_until_date is null 
                                or  keep_event_hidden_until_date >= "%s")', 
                                    date('Y-m-d')));
            $this->limit(4);

            $this->selectAdd('date_format(event_date, "%a %b %D, %Y") as human_date');
            $this->selectAdd('if(hour(event_date) > 0, date_format(event_date, "%l:%i%p"), "") as human_time');
            $this->orderBy('event_date asc');

            $co->page->confessArray($this->_query, $this->table .$this->_join);
            //$co->debugWrap(2);


            $this->find();
            while($this->fetch()){
                $res .= sprintf("<p>%s%s:&nbsp;<b>%s</b></p><p>%s</p><br>", 
                                $this->human_date, 
                                $this->human_time ? ' '. $this->human_time : '',
                                $this->description, 
                                $this->notes,
                                $publiconly ? '' : 
                                $co->recordButtons(&$this, false)
                    );
            }
            return $res;

        }

}
