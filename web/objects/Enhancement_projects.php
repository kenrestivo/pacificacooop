<?php
/**
 * Table Definition for enhancement_projects
 */
require_once 'DB/DataObject.php';

class Enhancement_projects extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'enhancement_projects';            // table name
    var $enhancement_project_id;          // int(32)  not_null primary_key unique_key auto_increment
    var $project_name;                    // string(255)  
    var $project_description;             // blob(16777215)  blob
    var $project_complete;                // date(10)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Enhancement_projects',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
