<?php
/**
 * Table Definition for access_levels
 */
require_once 'DB/DataObject.php';

class Access_levels extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'access_levels';                   // table name
    var $access_level_id;                 // int(32)  not_null primary_key unique_key
    var $short_name;                      // string(50)  
    var $description;                     // string(255)  
    var $const_name;                      // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Access_levels',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('description');


}
