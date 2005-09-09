<?php
/**
 * Table Definition for user_privileges
 */
require_once 'DB/DataObject.php';

class User_privileges extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'user_privileges';                 // table name
    var $privilege_id;                    // int(32)  not_null primary_key unique_key auto_increment
    var $user_id;                         // int(32)  
    var $group_id;                        // int(32)  
    var $user_level;                      // int(5)  
    var $group_level;                     // int(5)  
    var $realm_id;                        // int(32)  
    var $year_level;                      // int(32)  
    var $menu_level;                      // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('User_privileges',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	
	var $fb_fieldLabels = array ('user_id' => 'User Name', 
                                 'group_id' => 'Group',
                                 'user_level' => 'May do to their own data',
                                 'group_level' => 'May do to OTHER\'s data',
                                 'realm_id' => 'Data/Menu Realm',
                                 'year_level' => 'May do to OLD (not this school year) data'
                                 );

	var $fb_formHeaderText = 'User and Group Permissions';
	var $fb_shortHeader = 'User Permissions';
	var $fb_requiredFields = array('realm_id');

    
}