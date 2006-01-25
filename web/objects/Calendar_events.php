<?php
/**
 * Table Definition for calendar_events
 */
require_once 'DB/DataObject.php';

class Calendar_events extends CoopDBDO 
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
	var $fb_enumFields = array ('status', 'show_on_public_page');

	var $fb_linkDisplayFields = array( 'event_date','event_id');
    var $fb_fieldLabels = array(
        'event_id' => 'Event',
        'event_date' => 'Date (and time) of Event (MM/DD/YYYY), with optional "HH:MM AM/PM"',
        'status' => 'Status',
        'keep_event_hidden_until_date' => 'Hide this Event Until',
        'school_year' => 'School Year',
        'show_on_public_page' => 'Show on publicly-accessible home page?'
        );
	var $fb_formHeaderText =  'Calendar of Events';
	var $fb_shortHeader =  'Calendar';
    var $fb_fieldsToUnRender = array('keep_event_hidden_until_date');
    var $fb_enumFields = array('show_on_public_page', 'status');
    var $fb_defaults = array('status' => 'Active');
    var $fb_requiredFields = array('event_id', 'status', 'school_year', 
                                   'event_date');

    function fb_display_view(&$co)
        {
            // TODO: put this in linkconstraints so that popups use it too
            $this->orderBy('event_date asc');
            return $co->simpleTable(true, true);
        }


    function homepage_summary(&$co, $publiconly = false)
        {
            
            $publiconly = false;
            $ev =& $this->factory('events');
            $this->joinAdd($ev);

            // oh, this is a SILLY way to determine this
            if(empty($co->page->auth['token']) || $publiconly){
                $limit = 'public';
                $this->whereAdd('show_on_public_page = "Yes"');            
            }
            $this->status = 'Active';

            $this->whereAdd(sprintf('event_date >= "%s"', date('Y-m-d')));
            $this->whereAdd(
                sprintf(
                    '(keep_event_hidden_until_date is null 
                                or  keep_event_hidden_until_date >= "%s")', 
                                    date('Y-m-d')));
            $this->limit(8); // we get busy! 4 is too few

            $this->selectAdd('date_format(event_date, "%a %b %D, %Y") as human_date');
            $this->selectAdd('if(hour(event_date) > 0, date_format(event_date, "%l:%i%p"), "") as human_time');
            $this->orderBy('event_date asc');

            $co->page->confessArray($this->_query, $this->table .$this->_join);
            //$co->debugWrap(2);


            $this->find();
            while($this->fetch()){
                $res .= sprintf("<p>%s%s:&nbsp;<b>%s</b></p><p>%s&nbsp;%s</p><br />", 
                                $this->human_date, 
                                $this->human_time ? ' '. $this->human_time : '',
                                $this->url ? sprintf('<a href="%s">%s</a>',
                                                     $this->url, 
                                                     $this->description)
                                :$this->description, 
                                $this->notes,
                                $limit == 'public' ? '' : 
                                $co->recordButtons(
                                    &$this, 
                                    false, 
                                    array ('<span class="actions">(',
                                           ')</span>'))
                    );
            }
// XXX broken, don't know why. actionbuttons screwed up on members page IFF they have edit perms. weird... actionbuttons broken
            if(!$publiconly){
                $res .= '<p>For more events, click "View" above.</p>';
            }
            return $res;

        }

}
