<?php
/**
 * Table Definition for kids
 */
require_once 'DB/DataObject.php';

class Kids extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'kids';                            // table name
    var $kid_id;                          // int(32)  not_null primary_key unique_key auto_increment
    var $last_name;                       // string(255)  
    var $first_name;                      // string(255)  
    var $family_id;                       // int(32)  
    var $date_of_birth;                   // date(10)  binary

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Kids',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('last_name','first_name');
	var $fb_fieldLabels = array ('last_name' => 'Last Name');
	var $fb_linkOrderFields = array ('last_name', 'first_name');
	var $fb_fieldLabels = array(
		'last_name' => "Last Name",
		'family_id' => "Co-Op Family",
		'first_name' => "First Name",
		'date_of_birth' => 'Birthday'
	);
	var $fb_formHeaderText = "Students";
    var $fb_shortHeader = 'Kids';

	var $fb_requiredFields  = array('last_name', 'first_name', 'family_id');

    var $fb_joinPaths = array('school_year' => 'enrollment');

}
