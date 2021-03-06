<?php
/**
 * Table Definition for nags
 */
require_once 'DB/DataObject.php';

class Nags extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'nags';                            // table name
    var $nag_id;                          // int(32)  not_null primary_key unique_key auto_increment
    var $which_event;                     // string(10)  enum
    var $method_of_contact;               // string(11)  enum
    var $family_id;                       // int(32)  
    var $user_id;                         // int(32)  
    var $done;                            // datetime(19)  binary
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Nags',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('which_event', 'method_of_contact');

}
