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
    var $realm;                           // string(55)  
    var $user_level;                      // int(5)  
    var $group_level;                     // int(5)  
    var $realm_id;                        // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('User_privileges',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	
	var $fb_fieldLabels = array ('name' => 'User Name', 
							'family_id' => 'Co-Op Family');

	// from docs. very kewl
	function preGenerateForm() {

// the numbers, their display names, and the callback 'action' asssociated with them DUPLICATE OF AUTH.INC!
/// screw this. put it in the db
$accessnames = array(  
		  0 => array('None', NULL),
		100 => array('Summarize', 'summary'),
		200 => array('View', 'view'),
		500 => array('Edit', 'edit'),
		600 => array('Create', 'add'),
		700 => array('Delete', 'confirmdelete'),
		800 => array('Administer permissions for', NULL)
);
		foreach ($accessnames as $details => $priv){
			$privmap[$priv] = $details[0];
		}
		$foo = HTML_QuickForm::createElement('select', 'user_level', 
										  $this->fb_fieldLabels['user_level'],
										  $privmap);
		$this->preDefElements = array (&$foo);
			
	}		
	
}