<?php
/**
 * Table Definition for parent_ed_attendance
 */
require_once 'DB/DataObject.php';

class Parent_ed_attendance extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'parent_ed_attendance';            // table name
    var $parent_ed_attendance_id;         // int(32)  not_null primary_key unique_key auto_increment
    var $parent_id;                       // int(32)  
    var $calendar_event_id;               // int(32)  
    var $hours;                           // real(6)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Parent_ed_attendance',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function preGenerateForm(&$form)
        {
            $calev = $this->factory('calendar_events');
            $calev->school_year = $form->CoopForm->page->currentSchoolYear;
			$calev->whereAdd('event_id = 2'); // parent ed meeting
            $calev->orderBy('event_date asc');
            $calev->selectAdd('date_format(event_date, "%a %b %D, %Y") as human_date');
			$calev->find();
			$options[] = '-- CHOOSE ONE --';
			while($calev->fetch()){
                // TODO: use the linkfunction that surfs through linkdisplay
				$options[$calev->calendar_event_id] = $calev->human_date;
			}
			$el =& HTML_QuickForm::createElement(
                'select', 
                $form->CoopForm->prependTable('calendar_event_id'), 
                $this->fb_fieldLabels['calendar_event_id'], 
                &$options);
            
            $this->fb_preDefElements['calendar_event_id'] = $el;
			return $el;
            
        }


    //TODO: link constraints: from here to parents to kids to enrollment
    //also need (for reports) links to calevent for sorting by date!

	var $fb_linkDisplayFields = array('parent_id','calendar_event_id');
	var $fb_fieldLabels = array (
        'parent_id' => 'Co-Op Parent Attending',
        'calendar_event_id' => 'Meeting',
        'hours' => 'Hours attended'
		);
    var $fb_requiredFields = array ('parent_id', 'calendar_event_id');
	var $fb_formHeaderText = 'Parent Education Meeting Attendance';
	var $fb_shortHeader = 'Parent Ed';
    
    var $fb_joinPaths = array('school_year' => 'kids:enrollment');
    var $fb_defaults = array('hours' => 3);

	function fb_linkConstraints(&$co)
		{
            $fam = $this->factory('families');
            $par = $this->factory('parents');
            $par->joinAdd($fam);
            $this->joinAdd($par);

            $ev = $this->factory('calendar_events');
            $this->joinAdd($ev);

            if($co->isPermittedField(NULL) < ACCESS_VIEW && 
                $co->page->userStruct['family_id'])
            {
                /// XXX need to check that a familyid exists!
                $this->whereAdd('parents.family_id  = '. 
                                $co->page->userStruct['family_id']);
            }
            
            $this->orderBy('families.name, event_date');

            //XXX until i have year perms.
            $this->school_year = $co->page->currentSchoolYear;
            
            
            //$co->debugWrap(2);

        }

//     function fb_display_alert(&$co)
//         {
//         }


    /// custom displayview here


}
