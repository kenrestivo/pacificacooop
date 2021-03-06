<?php
/**
 * Table Definition for job_assignments
 */
require_once 'DB/DataObject.php';

class Job_assignments extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'job_assignments';                 // table name
    var $job_assignment_id;               // int(32)  not_null primary_key unique_key auto_increment
    var $job_description_id;              // int(32)  
    var $school_year;                     // string(50)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Job_assignments',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_formHeaderText =  'Job Assignments';
	var $fb_shortHeader =  'Assignments';
	var $fb_linkDisplayFields =  array('family_id','job_description_id');
	var $fb_fieldLabels = array (
		'job_description_id' => 'Job Description',
		'family_id' => 'Co-Op Family',
		'school_year' => 'School Year'
		);
	var $fb_requiredFields = array('job_description_id', 
								   'school_year',
								   'family_id');


    function fb_linkConstraints(&$co)
		{
            $descr = new CoopObject(&$co->page, 'job_descriptions', &$co);
            $co->protectedJoin($descr);
            $this->orderBy('summary');
            $co->constrainSchoolYear();
            $co->grouper();
        }

}
