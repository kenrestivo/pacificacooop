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

            $rastaquery = 
                'select distinct enrollment_id, kids.last_name as kid_last, concat(moms.first_name, " ", 
moms.last_name) as mom,
concat(dads.first_name, " ", dads.last_name) as dad, kids.first_name as kid_first, 
date_format(kids.date_of_birth, "%%m/%%d/%%Y") as human_date, 
families.address1, 
families.phone, 
families.email,
enrollment.monday, enrollment.tuesday, enrollment.wednesday, 
enrollment.thursday, enrollment.friday, job_descriptions.summary as school_job,
am_pm_session
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
where enrollment.school_year = "%s"
order by enrollment.am_pm_session, kids.last_name, kids.first_name';
            
            
            $this->fb_fieldLabels = 
                array ('am_pm_session' => 'Session',
                       'kid_last' => 'Last Name',
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
            
            // TODO: add in the split for am/pm, in to separate tables
            $this->query(sprintf($rastaquery, 
                               $co->page->currentSchoolYear,
                               $co->page->currentSchoolYear));


            foreach(array('monday', 'tuesday', 'wednesday', 
                          'thursday', 'friday') as $field)
            {
                // these are tinyint's; override that 
                $this->fb_displayCallbacks[$field] = 'checkWorkday';
            }


            $res .= $co->simpleTable(false);


            return $res;
            
        }

    function checkWorkday($val, $key)
        {
            print "HEY". $this->workday;
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

}
