<?php
/**
 * Table Definition for enhancement_hours
 */
require_once 'DB/DataObject.php';

class Enhancement_hours extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'enhancement_hours';               // table name
    var $enhancement_hour_id;             // int(32)  not_null primary_key unique_key auto_increment
    var $parent_id;                       // int(32)  
    var $enhancement_project_id;          // int(32)  not_null
    var $work_date;                       // date(10)  
    var $hours;                           // real(6)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Enhancement_hours',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
