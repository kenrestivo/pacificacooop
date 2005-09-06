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
	var $fb_formHeaderText = 'Parents';
	var $fb_shortHeader = 'Parents';

    var $fb_joinPaths = array('school_year' => 'kids:enrollment');


	function fb_linkConstraints()
		{

            $enrollment =  $this->factory('enrollment');

            // HACK! this is presuming VIEW, but in popup it could be EDIT
            if($this->CoopView->perms[NULL]['year'] < ACCESS_VIEW){
                ///NOTE! no dropout dates, need to show paretnts for enhancement
                $enrollment->whereAdd(
                    sprintf('enrollment.school_year = "%s"',
                        $this->CoopView->page->currentSchoolYear));
            }

            $kids =  $this->factory('kids');
            $kids->joinAdd($enrollment);


            $families =  $this->factory('families');
            $families->joinAdd($kids);


            $this->joinAdd($families);
            $this->orderBy('parents.last_name, parents.first_name');

            $this->selectAdd();
            $this->selectAdd("{$this->CoopView->table}.*");
            $this->selectAdd("max(enrollment.school_year) 
                                                as school_year");
            $this->groupBy("{$this->CoopView->table}.{$this->CoopView->pk}");


            //$this->CoopView->debugWrap(1);

			// ugly, but consisent. only shows families for this year



 		}


}
