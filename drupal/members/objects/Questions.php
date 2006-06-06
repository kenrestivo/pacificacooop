<?php
/**
 * Table Definition for questions
 */
require_once 'COOP/DBDO.php';

class Questions extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'questions';                       // table name
    var $question_id;                     // int(32)  not_null primary_key unique_key auto_increment
    var $question;                        // string(255)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Questions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('question');

	var $fb_fieldLabels = array(
        'question' => 'Question Text',
        'school_year' => 'School Year'
		);

	var $fb_formHeaderText =  'Poll Questions';
	var $fb_requiredFields = array('question',
								   'school_year',  
                                   );

    var $fb_shortHeader = 'Questions';
    var $fb_sizes = array('question' => 50);


}
