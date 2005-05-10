<?php
/**
 * Table Definition for families
 */
require_once 'DB/DataObject.php';

class Families extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'families';                        // table name
    var $family_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $name;                            // string(255)  
    var $phone;                           // string(20)  
    var $address1;                        // string(255)  
    var $email;                           // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Families',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('name');
	var $fb_fieldLabels = array (
		'name' => 'Family Name',
		'phone' => 'Phone Number',
		'address1' => 'Address',
		'email' => 'Email Address'
		);
	var $fb_requiredFields = array ('name', 'phone', 'address1');
	//var $fb_crossLinks = array(array('table' => 'families_income_join',
	//'fromField' => 'family_id', 'toField' => 'income_id'
	var $fb_linkNewValue = 1;
	
	function fb_linkConstraints()
		{
			// ugly, but consisent. only shows families for this year


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
			$enrol->whereAdd('dropout_date is null or dropout_date < "2000-01-01"');
			$kids->joinAdd($enrol);
			$this->joinAdd($kids);

			$this->selectAdd();
			$this->selectAdd('families.family_id, families.name');

		}




}
