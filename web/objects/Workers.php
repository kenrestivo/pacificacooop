<?php
/**
 * Table Definition for workers
 */
require_once 'DB/DataObject.php';

class Workers extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'workers';                         // table name
    var $worker_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $parent_id;                       // int(32)  
    var $workday;                         // string(9)  enum
    var $epod;                            // string(9)  enum
    var $am_pm_session;                   // string(2)  enum
    var $worker_for_donation;             // int(1)  
    var $brings_baby;                     // int(1)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Workers',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_fieldLabels = array ('worker_id' => 'Worker',
                                 'parent_id' => 'Parent',
                                 'workday' => 'Work Day',
                                 'epod' => 'EPOD Day',
                                 'am_pm_session' => 'Session',
                                 'worker_for_donation' => 'Worker For Donation?',
                                 'school_year' => 'School Year');
	var $fb_fieldsToRender = array('worker_id',
                                   'parent_id' ,
                                   'workday' ,
                                   'epod' ,
                                   'am_pm_session', 
                                   'worker_for_donation', 
                                   'school_year' );
								   
    var $fb_requiredFields = array('parent_id', 'workday', 'epod', 
                                   'am_pm_session', 'school_year');
	var $fb_formHeaderText =  'School Workday Schedule';
	var $fb_shortHeader =  'Workdays';
	var $fb_linkDisplayFields =  array('parent_id', 'am_pm_session');

    var $fb_enumFields = array ('epod', 'workday', 'am_pm_session');

     var $fb_joinPaths = array(
         'family_id' => 'parents'
         );

	function fb_linkConstraints(&$co)
		{

            $par = $this->factory('parents');

            $this->joinAdd($par);
            $this->selectAdd('family_id');

            /// AGAIN, nasty hack
//            if($co->perms[NULL]['year'] < ACCESS_VIEW){
                // TODO! support chooser
                $this->whereAdd(
                    "school_year = '{$co->page->currentSchoolYear}'");
                //          }

            $this->orderBy('am_pm_session, workday, parents.last_name, parents.first_name');
            

            //$co->debugWrap(5);
            
			// ugly, but consisent. only shows families for this year



 		}

    function  fb_display_summary(&$co)
        {

            ///XXX HACK! ispermitted field is returning NO for regular users anyway
            if($co->isPermittedField(null,true) <  ACCESS_VIEW){
                return '';
            }

            $res = '';
            $this->fb_formHeaderText = 'Workday Summary';
            $this->fb_recordActions = array();
            $this->fb_fieldsToRender = array('workday', 'AM', 'PM');
            $this->fb_fieldLabels = array(
                'workday' => 'Work Day',
                'AM' => 'Total AM',
                'PM' => 'Total PM'
                );
            $this->query(
                sprintf(
'select  
workday, sum(if(am_pm_session = "AM", 1,0 )) as AM, 
sum(if(am_pm_session = "PM", 1,0 )) as PM
from workers 
where school_year = "%s"
group by  workday
order by  workday',
                    $co->page->currentSchoolYear));
            $res .= $co->simpleTable(false);


            /// EPODS
            $co2 = new CoopView(&$co->page, $co->table, &$nothing);
            $co2->obj->fb_formHeaderText = 'EPOD Summary';
            $co2->obj->fb_recordActions = array();
            $co2->obj->fb_fieldsToRender = array('epod', 'AM', 'PM');
            $co2->obj->fb_fieldLabels = array(
                'epod' => 'EPOD',
                'AM' => 'Total AM',
                'PM' => 'Total PM'
                );

            $co2->obj->query(
                sprintf('select epod, sum(if(am_pm_session = "AM", 1,0 )) as AM, 
sum(if(am_pm_session = "PM", 1,0 )) as PM
from workers 
where school_year = "%s"
group by  epod
order by  epod',
                         $co->page->currentSchoolYear));
            
            $res .= $co2->simpleTable(false);
            return $res;

        }



}
