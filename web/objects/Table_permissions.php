<?php
/**
 * Table Definition for table_permissions
 */
require_once 'DB/DataObject.php';

class Table_permissions extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'table_permissions';               // table name
    var $table_permissions_id;            // int(32)  not_null primary_key unique_key auto_increment
    var $table_name;                      // string(255)  
    var $field_name;                      // string(255)  
    var $realm_id;                        // int(32)  
    var $user_level;                      // int(5)  
    var $group_level;                     // int(5)  
    var $menu_level;                      // int(5)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Table_permissions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_formHeaderText =  'Permissions and Realms for Tables';
	var $fb_shortHeader =  'Tables';

	var $fb_fieldLabels = array(
		'table_name' => 'Table',
        'field_name' => 'Field',
        'group_id' => 'Group',
        'realm_id' => 'Data/Menu Realm',
        'user_level' => 'Forbid this action level, to user\'s own data, unless user has permissions at or above this',
        'group_level' => 'Forbid this action to other families\' data, unless permitted',
        'menu_level' => 'Forbid users with group permissions below this from even being able to see the menu'
        );

	var $fb_requiredFields = array('table_name', 'realm_id');

    function fb_display_summary()
        {
            /// TODO: the simple summary of what i can do
            
        }

    function FOOfb_display_details()
        {
            /// TODO: the showperms from coopview
            
        }
    

}
