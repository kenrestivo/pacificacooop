<?php
/**
 * Table Definition for families_income_join
 */
require_once 'DB/DataObject.php';

class Families_income_join extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'families_income_join';            // table name
    var $families_income_join_id;         // int(32)  not_null primary_key unique_key auto_increment
    var $income_id;                       // int(32)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Families_income_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_fieldLabels = array ('income_id' => "Check", 
								 'family_id' => "Co-op Family");
}
