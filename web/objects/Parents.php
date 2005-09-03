<?php
/**
 * Table Definition for parents
 */
require_once 'DB/DataObject.php';

class Parents extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'parents';                         // table name
    var $parent_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $last_name;                       // string(255)  
    var $first_name;                      // string(255)  
    var $type;                            // string(7)  enum
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Parents',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('last_name','first_name');
	var $fb_fieldLabels = array (
		'last_name' => 'Last Name',
		'first_name' => 'First Name',
		'family_id' => 'Co-Op Family',
		'type' => 'Parent Type',
		'worker' => 'Main worker'
		);
	var $fb_linkOrderFields = array ('last_name', 'first_name');
	var $fb_enumFields = array ('type', 'worker');
	var $fb_fieldsToRender = array ('last_name', 'first_name', 'type', 'worker');
	var $fb_formHeaderText = 'Parents';
	var $fb_shortHeader = 'Parents';

    var $fb_joinPaths = array('school_year' => 'kids:enrollment');

	function fb_linkConstraints()
		{
			// ugly, but consisent. only shows parents for this year

			//$this->debugLevel(2);
			$fam = DB_DataObject::factory('families'); 
 			if (PEAR::isError($fam)){
				user_error("Tickets.php::linkconstraint(): db badness", 
						   E_USER_ERROR);
			}
			
			$kids = DB_DataObject::factory('kids'); 
 			if (PEAR::isError($kids)){
				user_error("Tickets.php::linkconstraint(): db badness", 
						   E_USER_ERROR);
			}
			
			$enrol = DB_DataObject::factory('enrollment'); 
 			if (PEAR::isError($enrol)){
				user_error("Tickets.php::linkconstraint(): db badness", 
						   E_USER_ERROR);
			}
			$enrol->school_year = findSchoolYear();
			//$enrol->whereAdd('dropout_date is null or dropout_date < "2000-01-01"');
			$kids->joinAdd($enrol);
			$fam->joinAdd($kids);
			$this->joinAdd($fam);

			$this->selectAdd();
			$this->selectAdd('parents.parent_id, parents.first_name, parents.last_name');

		}



}
