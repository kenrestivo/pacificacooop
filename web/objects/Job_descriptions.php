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
	var $fb_fieldLabels = array (
		'summary' => "Name of Position",
		'long_description' => "Job Description",
		'family_type' => "New or Returning Family?",
		'board_position' => "Board-level position?",
		'free_tuition_days' => "Days Per Week of Free Tuition",
		'free_tuition_months' => "Months of Free Tuition Days"
		);
	var $fb_textFields = array('long_description');
	var $fb_enumFields = array ('board_position', 'family_type');
	var $fb_requiredFields = array('long_description', 'summary');
    var $fb_longHeader = 'The virtual job description binder.';
	var $fb_shortHeader = 'Descriptions';
    // NOTE ! do NOT schoolyearify the jobdescriptions. show all years.
    // XXX Ryeah, but, dumbass, you need to EDIT only this years'
    var $fb_joinPaths = array('family_id' => 'job_assignments');
	var $fb_crossLinks = array(array('table' => 'job_assignments', 
									 'toTable' => 'families',
									 'toField' => 'family_id',
									 'type' => 'select'));


	// save it until i square away schoolyear
//  	var $fb_crossLinks = array(array('table' => 'job_descriptions_families_join', 
//  									 'toTable' => 'families',
//  									 'toField' => 'family_id',
//  									 'type' => 'select'));

    function postGenerateForm(&$form)
        {
            //confessObj($form, 'form');
//             confessArray(get_class_methods($form->CoopForm), 
//             get_class($form->CoopForm));
            $el =& $form->getElement(
                $form->CoopForm->prependTable('long_description'));
            //confessObj($el, get_class($el));
            $el->setRows(25);
            $el->setCols(80);
        }


/////NOT YET! first fix the form linking, doesn't work in form.
/////and perms for job_descr: don't want people editing the days tuition
// 	function fb_linkConstraints(&$co)
// 		{
    
//             $ass =  $this->factory('job_assignments');

//             $this->joinAdd($ass);

//             $this->orderBy('summary');
//             $this->groupBy("{$co->table}.{$co->pk}");

//             $co->debugWrap(4);

// 			ugly, but consisent. only shows families for this year



//  		}


}
