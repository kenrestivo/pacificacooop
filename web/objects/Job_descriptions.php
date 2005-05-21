<?php
/**
 * Table Definition for job_descriptions
 */
require_once 'DB/DataObject.php';

class Job_descriptions extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'job_descriptions';                // table name
    var $job_description_id;              // int(32)  not_null primary_key unique_key auto_increment
    var $summary;                         // string(255)  
    var $long_description;                // blob(16777215)  blob
    var $family_type;                     // string(9)  enum
    var $board_position;                  // string(3)  enum
    var $free_tuition_days;               // int(3)  
    var $free_tuition_months;             // int(3)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Job_descriptions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_formHeaderText =  'Job Descriptions';
	var $fb_linkDisplayFields = array('summary');
	var $fb_fieldsToRender = array(
		'summary',
		'board_position',
		'free_tuition_days',
		'free_tuition_months'
		);
	var $fb_fieldLabels = array (
		'summary' => "Name of Position",
		'long_description' => "Job Description",
		'board_position' => "Board-level position?",
		'free_tuition_days' => "Days Per Week of Free Tuition",
		'free_tuition_months' => "Months of Free Tuition Days"
		);
	var $fb_textFields = array('long_description');
	var $fb_enumFields = array ('board_position');
	var $fb_requiredFields = array('long_description', 'summary');
	
	// save it until i square away schoolyear
//  	var $fb_crossLinks = array(array('table' => 'job_descriptions_families_join', 
//  									 'toTable' => 'families',
//  									 'toField' => 'family_id',
//  									 'type' => 'select'));


}
