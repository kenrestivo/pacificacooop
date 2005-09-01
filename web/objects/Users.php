<?php
/**
 * Table Definition for users
 */
require_once 'DB/DataObject.php';

class Users extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'users';                           // table name
    var $user_id;                         // int(32)  not_null primary_key unique_key auto_increment
    var $password;                        // string(255)  
    var $name;                            // string(255)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Users',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_fieldLabels = array ('name' => 'User Name', 
							'family_id' => 'Co-Op Family');
	//var $fb_fieldsToRender = array('name', 'family_id');
	var $fb_linkDisplayFields = array('name');
	var $fb_formHeaderText = 'System Users';
	var $fb_shortHeader = 'Users';

	// from docs. very kewl
	function preGenerateForm() {
		$this->fb_preDefElements['password'] = 
			HTML_QuickForm::createElement('password', 'password', 
										  'Password');
		
}
	
	function preProcessForm(&$data) {
//		confessArray($data, "preproecessdata");
		if(isset($data['password'])) {
			if($data['password'] != $this->password) {
				$data['password'] = md5($data['password']);
			}
		}
	}

}
