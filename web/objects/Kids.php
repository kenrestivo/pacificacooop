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
    var $allergies;                       // string(255)  
    var $doctor_id;                       // int(32)  

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
		'date_of_birth' => 'Birthday',
		'allergies' => 'Allergies',
        'doctor_id' => 'Doctor'
	);
	var $fb_formHeaderText = "Co-Op Students";
    var $fb_shortHeader = 'Students';

	var $fb_requiredFields  = array('last_name', 'first_name', 'family_id');

    var $fb_joinPaths = array('school_year' => 'enrollment');

	function fb_linkConstraints(&$co)
		{

            $enrollment =  $this->factory('enrollment');


            $this->orderBy('last_name, first_name');

            // HACK! this is presuming VIEW, but in popup it could be EDIT
            if($co->perms[NULL]['year'] < ACCESS_VIEW){
                $enrollment->whereAdd(
                    '(dropout_date is null or dropout_date < "2000-01-01")');



                $this->joinAdd($enrollment);

                // IMPORTANT! otherwise it matches old year
                $this->selectAdd('max(school_year) as school_year');
                $this->groupBy("{$co->table}.{$co->pk}");


                // TODO! support chooser
                $this->whereAdd(
                    "enrollment.school_year = '{$co->page->currentSchoolYear}'");




                $this->selectAdd();
                $this->selectAdd("{$co->table}.*");
                $this->selectAdd("max(enrollment.school_year) 
                                                as school_year");
                $this->groupBy("{$co->table}.{$co->pk}");


            }

            //$co->debugWrap(2);

			// ugly, but consisent. only shows families for this year



 		}


}
