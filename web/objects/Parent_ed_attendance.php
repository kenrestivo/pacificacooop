<?php
/**
 * Table Definition for parent_ed_attendance
 */
require_once 'DB/DataObject.php';

class Parent_ed_attendance extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'parent_ed_attendance';            // table name
    var $parent_ed_attendance_id;         // int(32)  not_null primary_key unique_key auto_increment
    var $parent_id;                       // int(32)  
    var $calendar_event_id;               // int(32)  
    var $hours;                           // real(6)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Parent_ed_attendance',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


    //TODO: link constraints: from here to parents to kids to enrollment
    //also need (for reports) links to calevent for sorting by date!


}
