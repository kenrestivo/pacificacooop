<?php
/**
 * Table Definition for realms
 */
require_once 'DB/DataObject.php';

class Realms extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'realms';                          // table name
    var $realm_id;                        // int(32)  not_null primary_key unique_key auto_increment
    var $realm;                           // string(255)  
    var $meta_realm_id;                   // int(32)  
    var $short_description;               // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Realms',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('realm');
	var $fb_formHeaderText = 'Data Realms';
	var $fb_shortHeader = 'Realms';


}
 