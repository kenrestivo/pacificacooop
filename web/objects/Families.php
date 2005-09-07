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
	var $fb_fieldsToRender = array ('name', 'phone', 'address1', 'email');
    var $fb_formHeaderText = "Co-Op Member Families";
    var $fb_shortHeader = "Families";
    var $fb_joinPaths = array('school_year' => 'kids:enrollment');


 	function fb_linkConstraints(&$co)
		{

            $enrollment =  $this->factory('enrollment');

            // HACK! this is presuming VIEW, but in popup it could be EDIT
            if($co->perms[NULL]['year'] < ACCESS_VIEW){
                $enrollment->whereAdd(
                    '(dropout_date is null or dropout_date < "2000-01-01")');
                $enrollment->whereAdd(
                    sprintf('school_year = "%s"',
                        $co->page->currentSchoolYear));
            }

            $kids =  $this->factory('kids');
            $kids->joinAdd($enrollment, '');

            $this->joinAdd($kids);
            $this->orderBy('families.name');
            $this->groupBy('families.name');

            $this->selectAdd('max(school_year) as school_year');


            //$co->debugWrap(5);

			// ugly, but consisent. only shows families for this year



 		}




}