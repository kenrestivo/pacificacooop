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
    var $start_date;                      // date(10)  
    var $dropout_date;                    // date(10)  

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
		'dropout_date' => "Drop Date"
		);
//	var $fb_fieldsToRender = array('school_year', 'am_pm_session', 'start_date', 'dropout_date');

}
