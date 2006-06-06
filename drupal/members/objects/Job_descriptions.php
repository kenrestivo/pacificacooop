<?php
/**
 * Table Definition for job_descriptions
 */
require_once 'DB/DataObject.php';

class Job_descriptions extends CoopDBDO 
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
    var $free_tuition_start_month;        // string(9)  enum
    var $free_tuition_end_month;          // string(9)  enum
    var $_cache_long_description;         // string(255)  

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
		'free_tuition_days' => "Child-Days Per Week of Free Tuition",
		'free_tuition_start_month' => "First Month of Free Tuition",
		'free_tuition_end_month' => "Last Month of Free Tuition"
		);
	var $fb_textFields = array('long_description');
	var $fb_enumFields = array ('board_position', 'family_type',
                                'free_tuition_end_month', 
                                'free_tuition_start_month');
	var $fb_requiredFields = array('long_description', 'summary');

	var $fb_shortHeader = 'Descriptions';

    var $fb_joinPaths = array('family_id' => 'job_assignments');

    var $fb_defaults = array('free_tuition_start_month' => 'None',
                             'free_tuition_end_month' => 'None');

	// will default to THIS schoolyear
  	var $fb_crossLinks = array(array('table' => 'job_assignments', 
  									 'toTable' => 'families',
  									 'toField' => 'family_id',
  									 'type' => 'select'));

    function postGenerateForm(&$form)
        {
            //confessObj($form, 'form');
//             confessArray(get_class_methods($form->CoopForm), 
//             get_class($form->CoopForm));
            $el =& $form->getElement(
                $form->CoopForm->prependTable('long_description'));
            if($form->CoopForm->page->debug > 3){
                confessObj($el, get_class($el));
            }
            $el->setRows(25);
            $el->setCols(80);
        }


	function fb_linkConstraints(&$co)
		{
    
            $ass = new CoopObject(&$co->page, 'job_assignments', &$co);

            $co->protectedJoin($ass);

            // i apologise for this.
            // XXX NASTY HACK to force school year and family if no assignment.
            $co->obj->selectAdd(
                sprintf('if(job_assignments.family_id > 0, job_assignments.family_id, %d) as family_id',
                        $co->page->userStruct['family_id']));

            // this too
            $co->obj->selectAdd(
                sprintf('if(job_assignments.school_year > 0, job_assignments.school_year, "%s") as school_year',
                        $co->getChosenSchoolYear()));


            $co->constrainFamily();
            $co->constrainSchoolYear();
            $co->orderByLinkDisplay();
            $co->grouper();
 		}


}
