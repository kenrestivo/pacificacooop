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

            $this->selectAdd();
            $this->selectAdd("{$co->table}.*");
            //$co->debugWrap(2);

			// ugly, but consisent. only shows families for this year



 		}





    function fb_display_details()
        {

            $top =& $this->CoopView;
            $mi = $top->pk;
            $cid = $this->{$top->pk};
            $cp =& $top->page;

            
            $sy =findSchoolYear();
	

            //print "CHECKING $table<br>";
            $top->obj->$mi = $cid;
            $top->obj->find(true);
            print $top->horizTable();


            // need that familyid!
            $top->obj->getLinks();
            $family_id = $top->obj->_kid_id->family_id;
            //confessObj($top->obj, 'enrol');

            $view = new CoopView(&$cp, 'families', &$top);
            $view->obj->family_id = $family_id;
            print $view->simpleTable();



            // get all the kids... who are enrolled. gah.
            $subenrol = new CoopView(&$cp, 'enrollment', &$top);
            $subenrol->obj->whereAdd("$mi = $cid");
            $subenrol->obj->whereAdd(sprintf('school_year = "%s"', 
                                             $subenrol->page->currentSchoolYear));
            print $subenrol->simpleTable();

            //parents are easier. most of the time ;-)
            $view = new CoopView(&$cp, 'parents', &$top);
            $view->obj->family_id = $family_id;
            $view->obj->orderBy('type asc');
            //TODO actionbutons to edit
            print $view->simpleTable();
	
            //workers
            $view = new CoopView(&$cp, 'workers', &$top);
            $view->obj->whereAdd("$mi = $cid");
            $view->obj->whereAdd(sprintf('enrollment.school_year = "%s"', 
                                         $view->page->currentSchoolYear));
            print $view->simpleTable();

            // standard audit trail, for all details
            $aud =& new CoopView(&$cp, 'audit_trail', &$atd);
            $aud->obj->table_name = $top->table;
            $aud->obj->index_id = $this->{$top->pk};
            $aud->obj->orderBy('updated desc');
            print $aud->simpleTable();




        }
}
