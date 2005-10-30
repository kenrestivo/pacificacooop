<?php
/**
 * Table Definition for parent_ed_attendance
 */
require_once 'DB/DataObject.php';

class Parent_ed_attendance extends CoopDBDO 
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


            $par = new CoopObject(&$co->page, 'parents', &$co);
            $par->linkConstraints();
            $co->protectedJoin($par);
           

            $ev = new CoopObject(&$co->page, 'calendar_events', &$co);
            $co->protectedJoin($ev);

            if($co->isPermittedField(NULL) < ACCESS_VIEW && 
                $co->page->userStruct['family_id'])
            {
                // print "HEY!";
                /// XXX need to check that a familyid exists!
                $this->whereAdd(sprintf('parents.family_id  = %d', 
                                $co->page->userStruct['family_id']));
            }
            
            $this->orderBy('families.name, event_date');

            
            
            ///$co->debugWrap(2);

        }

//     function fb_display_alert(&$co)
//         {
//         }


    /// custom displayview here
    function fb_display_view(&$co)
        {
            $res = '';

            /// THE CHOOSER FORM
            $syform =& new HTML_QuickForm($cp->table . 'chooser', false, false, 
                                          false, false, true);


            ///meetings
            $meetings[0] ='ALL';
            $cal =& new CoopView(&$co->page, 'calendar_events', &$co);
            $cal->obj->whereAdd('event_id = 2'); ///XXX HARDCODED! parent ed
            $cal->obj->whereAdd('event_date <= now()');
            $cal->find(true); // go get, including any constraints/sorts
            while($cal->obj->fetch()){
                $nice = $cal->toArrayWithKeys();
                // NOTE the id will not be in toarraywithkeys, use obj
                $meetings[$cal->obj->calendar_event_id] = $nice['event_date'];
            }

            $sel =& $syform->addElement('select', 'calendar_event_id', 
                                        'Meeting', 
                                       $meetings,
                                       array('onchange' =>
                                             'this.form.submit()'));


            // families
            $families[0] ='ALL';
            $fam =& new CoopView(&$co->page, 'families', &$co);
            $fam->constrainSchoolYear(true);
            //$fam->debugWrap(2);
            $fam->find(true); // go get, including any constraints/sorts
            while($fam->obj->fetch()){
                $families[$fam->obj->family_id] = $fam->obj->name;
            }

            if($co->perms[null]['group'] >= ACCESS_VIEW){
                $famsel =& $syform->addElement('select', 'family_id', 
                                               'Family', 
                                               $families,
                                               array('onchange' =>
                                                     'this.form.submit()'));
            }


            if($sid = thruAuthCore($co->page->auth)){
                $syform->addElement('hidden', 'coop', $sid); 
            }
            $syform->addElement('hidden', 'table', $co->table); // XXX hack

            // need change button?
            $syform->addElement('submit', 'savebutton', 'Change');
                
            
            //COOL! this is the first place i am using vars->last
            $syform->setDefaults(array('calendar_event_id' => $co->page->vars['last']['calendar_event_id']));
            
            // fucking PHP sucks. i so wish it allowed $sel->getValue()[0] here.
            $foo = $sel->getValue();
            $cal_id = $foo[0];
            
            if($famsel){
                $bar = $famsel->getValue();
                $family_id = $bar[0];
            }

            $res .= $syform->toHTML();

            
            $cal_id && $this->whereAdd(sprintf('%s.calendar_event_id = %d', 
                                    $co->table,
                                    $cal_id));
            // XXX i hard-code parents in here
            // because i know not how else to dismbiguate it
            // NOTE: you'll be in a world of hurt if they have view group perms
            $family_id && $this->whereAdd(sprintf('parents.family_id = %d', 
                                    $family_id));

            $this->fb_fieldLabels =  array_merge(array('name' => 'Co-Op Family'),
                                                 $this->fb_fieldLabels);



            $this->selectAdd('families.name');
            //$co->debugWrap(2);
            $res .= $co->simpleTable();

            return $res;
        }

}
