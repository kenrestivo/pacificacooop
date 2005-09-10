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
    var $short_description;               // string(255)  
    var $meta_realm_id;                   // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Realms',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('realm');
	var $fb_formHeaderText = 'Data Realms';
	var $fb_shortHeader = 'Realms';
    // nice idea, but no. i need to define perms for each
// 	var $fb_crossLinks = array(array('table' => 'user_privileges', 
// 									 'toTable' => 'users',
// 									 'toField' => 'user_id',
// 									 'type' => 'select'));
    var $fb_fieldsToRender = array ('short_description', 'meta_realm_id');
    var $fb_linkDisplayFields = array ('short_description');
    var $fb_fieldLabels = array('realm' => 'Id',
                                'short_description' => 'Data/Menu Realm',
                                'meta_realm_id' => 'Is Subrealm of');

}
 