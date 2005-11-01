<?php
/**
 * Table Definition for parents
 */
require_once 'DB/DataObject.php';

class Parents extends CoopDBDO 
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
    var $fb_requiredFields = array('last_name', 'first_name', 
                                   'family_id', 'type');

    var $fb_joinPaths = array('school_year' => 'kids:enrollment');

	function fb_linkConstraints(&$co)
		{

            $this->orderBy('parents.last_name, parents.first_name');

            
            
            $families =&  new CoopObject(&$co->page, 'families', &$co);
            $families->linkConstraints();
            
            $co->protectedJoin($families);
        
            $co->constrainSchoolYear();
    
            /// XXX do i really need this
            /// now that i specify table.schoolyear in link?
            /// is "outer" join sufficient to work this?
            $this->selectAdd();
            $this->selectAdd("{$co->table}.*");

            ///XXX this is redunannt. it is done elsewhere now.
            $this->groupBy("{$co->table}.{$co->pk}");
            

            //confessObj($co, 'sure it is an objec');
            //$co->debugWrap(4);

			// ugly, but consisent. only shows families for this year



 		}


}
