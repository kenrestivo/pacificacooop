<?php
/**
 * Table Definition for sources
 */
require_once 'DB/DataObject.php';

class Sources extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'sources';                         // table name
    var $source_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $description;                     // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sources',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array ('description');
}
