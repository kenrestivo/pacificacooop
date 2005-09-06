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
    var $workday;                         // string(40)  set
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
                                 'brings_baby' => 'Bringing Baby?',
                                 'school_year' => 'School Year');
	var $fb_fieldsToRender = array('worker_id',
                                   'parent_id' ,
                                   'workday' ,
                                   'epod' ,
                                   'am_pm_session', 
                                   'worker_for_donation', 
                                   'brings_baby', 
                                   'school_year' );
								   
	var $fb_formHeaderText =  'School Workday Schedule';
	var $fb_shortHeader =  'Workdays';
	var $fb_linkDisplayFields =  array('parent_id', 'am_pm_session');

     var $fb_joinPaths = array(
         'family_id' => 'parents'
         );

	function fb_linkConstraints()
		{

            $enrollment =  $this->factory('enrollment');

            // HACK! this is presuming VIEW, but in popup it could be EDIT
            //ALWAYS show ONLY this years
                $enrollment->whereAdd(
                    'dropout_date is null or dropout_date < "2000-01-01"');
                $enrollment->whereAdd(
                    sprintf('enrollment.school_year = "%s"',
                        $this->CoopView->page->currentSchoolYear));


            $kids =  $this->factory('kids');
            $kids->joinAdd($enrollment, '');


            $families =  $this->factory('families');
            $families->joinAdd($kids);


            $parents =  $this->factory('parents');
            $parents->joinAdd($families);


            $this->joinAdd($parents);
            $this->orderBy('am_pm_session, workday, parents.last_name, parents.first_name');


            /// AGAIN, nasty hack
            if($this->CoopView->perms[NULL]['year'] < ACCESS_VIEW){
                // TODO! support chooser
                $this->whereAdd(
                    "enrollment.school_year = '{$this->CoopView->page->currentSchoolYear}'");
            }


            
            $this->selectAdd();
            $this->selectAdd("{$this->CoopView->table}.*");
            $this->selectAdd("max(enrollment.school_year) 
                                                as school_year");
            $this->groupBy("{$this->CoopView->table}.{$this->CoopView->pk}");

            //$this->CoopView->debugWrap(5);
            
			// ugly, but consisent. only shows families for this year



 		}



}
