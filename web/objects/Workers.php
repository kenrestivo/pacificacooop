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

}
