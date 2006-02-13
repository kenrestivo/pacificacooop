<?php
/**
 * Table Definition for nag_indulgences
 */
require_once 'DB/DataObject.php';

class Nag_indulgences extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'nag_indulgences';                 // table name
    var $nag_indulgence_id;               // int(32)  not_null primary_key unique_key auto_increment
    var $note;                            // string(255)  
    var $granted_date;                    // date(10)  binary
    var $indulgence_type;                 // string(21)  enum
    var $family_id;                       // int(32)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Nag_indulgences',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_fieldLabels = array (
		'note' => "Reason",
		'granted_date' => 'Date Granted',
		'indulgence_type' => 'Type',
		'family_id' => 'Co-Op Family',
		'school_year' => 'School Year'
		);

var $fb_formHeaderText = 'Springfest Nag Indulgences';


var $fb_shortHeader = 'Indulgences';

var $fb_requiredFields = array(
   'family_id',
   'indulgence_type',
   'granted_date',
   'school_year'
);
    var $fb_enumFields = array ('indulgence_type');

}
