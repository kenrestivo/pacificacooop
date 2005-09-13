<?php
/**
 * Table Definition for enrollment
 */
require_once 'DB/DataObject.php';

class Enrollment extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'enrollment';                      // table name
    var $enrollment_id;                   // int(32)  not_null primary_key unique_key auto_increment
    var $kid_id;                          // int(32)  
    var $school_year;                     // string(50)  
    var $am_pm_session;                   // string(2)  enum
    var $start_date;                      // date(10)  binary
    var $dropout_date;                    // date(10)  binary
    var $monday;                          // int(1)  
    var $tuesday;                         // int(1)  
    var $wednesday;                       // int(1)  
    var $thursday;                        // int(1)  
    var $friday;                          // int(1)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Enrollment',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('am_pm_session');
	var $fb_fieldLabels = array(
		'kid_id' => 'Student',
		'school_year' => 'School Year',
		'am_pm_session' => "Session",
		'start_date' => 'Start Date',
		'dropout_date' => "Drop Date",
        'monday' => 'M',
        'tuesday' => 'Tu',
        'wednesday' => 'W',
        'thursday' => 'Th',
        'friday' => 'F',
		);
    
	var $fb_requiredFields  = array('school_year', 'am_pm_session', 
									'start_date', 'kid_id');
	var $fb_linkDisplayFields = array('school_year', 'am_pm_session');
    var $fb_orderBy = 'school_year, am_pm_session';
    
    var $fb_formHeaderText = 'Enrollment Roster';
//var $fb_usePage = 'newroster.php';
    
    var $fb_shortHeader = 'Roster';
    
    var $fb_joinPaths = array('family_id' => 'kids'); 
    

	function fb_linkConstraints(&$co)
		{

            $kids =  $this->factory('kids');


            $this->joinAdd($kids, 'left');

                // TODO! support chooser
            $this->whereAdd(
                '(dropout_date is null or dropout_date < "2000-01-01")');
            
            $this->school_year = $co->page->currentSchoolYear;

            $this->orderBy('am_pm_session, last_name, first_name');

            //TODO: selectadd familyid!
            $this->selectAdd();
            $this->selectAdd("{$co->table}.*");
            //$co->debugWrap(2);

			// ugly, but consisent. only shows families for this year



 		}





    function fb_display_details(&$co)
        {

            $res = '';
            $top =& $co;
            $mi = $top->pk;
            $cid = $this->{$top->pk};
            $cp =& $top->page;

            
            $sy =findSchoolYear();
	

            //print "CHECKING $table<br>";
            $top->obj->$mi = $cid;
            $top->obj->find(true);
            $res .= $top->horizTable();


            // need that familyid!
            $top->obj->getLinks();
            $family_id = $top->obj->_kid_id->family_id;
            //confessObj($top->obj, 'enrol');

            $view = new CoopView(&$cp, 'families', &$top);
            $view->obj->family_id = $family_id;
            $res .= $view->simpleTable();



            // get all the kids... who are enrolled. gah.
            $subenrol = new CoopView(&$cp, 'enrollment', &$top);
            $subenrol->obj->whereAdd("$mi = $cid");
            $subenrol->obj->whereAdd(sprintf('school_year = "%s"', 
                                             $subenrol->page->currentSchoolYear));
            $res .= $subenrol->simpleTable();

            //parents are easier. most of the time ;-)
            $view = new CoopView(&$cp, 'parents', &$top);
            $view->obj->family_id = $family_id;
            $view->obj->orderBy('type asc');
            //TODO actionbutons to edit
            $res .= $view->simpleTable();
	
            //workers
            $view = new CoopView(&$cp, 'workers', &$top);
            $view->obj->whereAdd("family_id = $family_id");
            $view->obj->school_year = $view->page->currentSchoolYear;
            $res .= $view->simpleTable();

            // standard audit trail, for all details
            $aud =& new CoopView(&$cp, 'audit_trail', &$atd);
            $aud->obj->table_name = $top->table;
            $aud->obj->index_id = $this->{$top->pk};
            $aud->obj->orderBy('updated desc');
            $res .= $aud->simpleTable();

            return $res;

        }

    function fb_display_view(&$co)
        {

            
            // XXX not very object-oriented, but, fuck it, it works
            $res .= $this->_prepareView(&$co, 'AM');  

            // i need separate copies for am/pm
            $co2 = new CoopView(&$co->page, $co->table, &$nothing);
            $res .= $co2->obj->_prepareView(&$co2, 'PM');

            return $res; 

            
        }

    function _prepareView(&$co, $session)
        {
            // TODO: do not show dropped enrollment!

            $rastaquery = 
                'select  enrollment_id, kids.last_name as kid_last, 
concat(moms.first_name, " ", 
moms.last_name) as mom,
concat(dads.first_name, " ", dads.last_name) as dad, 
kids.first_name as kid_first, 
date_format(kids.date_of_birth, "%%m/%%d/%%Y") as human_date, 
families.address1, 
families.phone, 
families.email,
enrollment.monday, enrollment.tuesday, enrollment.wednesday, 
enrollment.thursday, enrollment.friday, job_descriptions.summary as school_job,
enrollment.am_pm_session, enrollment.start_date, enrollment.dropout_date,
workers.workday, workers.epod, workers.brings_baby, workers.am_pm_session
from enrollment
left join kids on enrollment.kid_id = kids.kid_id
left join parents as dads 
on dads.family_id = kids.family_id and dads.type <> "Mom"
left join parents as moms
on moms.family_id = kids.family_id and moms.type = "Mom"
left join families on kids.family_id = families.family_id
left join job_assignments 
on kids.family_id = job_assignments.family_id 
and job_assignments.school_year = "%s"
left join job_descriptions 
on job_descriptions.job_description_id = job_assignments.job_description_id
left join workers on (workers.parent_id = moms.parent_id or workers.parent_id =dads.parent_id) and workers.school_year = "%s"
where enrollment.school_year = "%s" and enrollment.am_pm_session = "%s"
group by enrollment_id
order by enrollment.am_pm_session, kids.last_name, kids.first_name';
            
            $co->obj->fb_fieldLabels = 
                array ('kid_last' => 'Last Name',
                       'mom' => 'Mom Name',
                       'dad' => 'Dad/Partner',
                       'kid_first' => 'Child',
                       'human_date' => 'DOB',
                       'address1'=> 'Address',
                       'phone' => 'Phone',
                       'email'=> 'Email',
                       'monday' => 'M',
                       'tuesday' => 'Tu',
                       'wednesday' => 'W',
                       'thursday' => 'Th',
                       'friday' => 'F',
                       'school_job' => 'School Job',
                       'start_date' => 'Start Date',
                       'dropout_date' => "Drop Date",
                       );
            

            $this->fb_formHeaderText .= 
                " {$co->page->currentSchoolYear} $session session";

            foreach(array('monday', 'tuesday', 'wednesday', 
                          'thursday', 'friday') as $field)
            {
                // these are tinyint's; override that 
                $co->obj->fb_displayCallbacks[$field] = 'checkWorkday';
            }

            $co->obj->query(sprintf($rastaquery, 
                                 $co->page->currentSchoolYear,
                                 $co->page->currentSchoolYear,
                                 $co->page->currentSchoolYear, 
                                 $session));


            return $co->simpleTable(false);

        }


    function checkWorkday($val, $key)
        {
            if(strtolower($this->workday) == $key){
                if($this->brings_baby){
                    return 'B';
                }
                return 'W';
            }
            if(strtolower($this->epod) == $key){
                return 'E';
            }
            return $val ? 'c' : '';
        }


    function  fb_display_summary(&$co)
        {
            foreach(array('monday', 'tuesday', 'wednesday', 
                          'thursday', 'friday') as $day){
                $this->fb_displayFormat[$day] = '%d';
            }

            $this->fb_fieldsToUnRender = array('start_date', 
                                               'dropout_date',
                                               'kid_id');

            $this->fb_formHeaderText = 'Enrollment Session Summary';
            $this->fb_recordActions = array();
            $this->query(
                sprintf(
                    'select  am_pm_session, sum(monday) as monday, sum(tuesday) as tuesday, sum(wednesday) as wednesday, sum(thursday) as thursday, sum(friday) as friday from enrollment where enrollment.school_year = "2005-2006" group by am_pm_session order by enrollment.am_pm_session',
                    $co->page->currentSchoolYear));
            return $co->simpleTable(false);

        }


}
