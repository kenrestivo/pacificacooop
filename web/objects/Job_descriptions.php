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
    var $board_position;                  // int(1)  
    var $tuition_type;                    // string(4)  enum

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
		'tuition_type'
		);
	var $fb_fieldLabels = array (
		'summary' => "Name of Position",
		'long_description' => "Job Description",
		'board_position' => "Board-level position?",
		'tuition_type' => "Tuition Type"
		);
	var $fb_textFields = array('long_description');

}
