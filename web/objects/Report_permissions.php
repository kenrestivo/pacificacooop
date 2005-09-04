<?php
/**
 * Table Definition for report_permissions
 */
require_once 'DB/DataObject.php';

class Report_permissions extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'report_permissions';              // table name
    var $report_permissions_id;           // int(32)  not_null primary_key unique_key auto_increment
    var $report_name;                     // string(255)  
    var $page;                            // string(255)  
    var $realm_id;                        // int(32)  
    var $user_level;                      // int(5)  
    var $group_level;                     // int(5)  
    var $menu_level;                      // int(5)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Report_permissions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_formHeaderText =  'Report Permissions';
    var $fb_shortHeader = 'Reports';

	var $fb_fieldLabels = array(
		'report_name' => 'Report Short Name',
        'realm_id' => 'Data/Menu Realm',
        'user_level' => 'Forbid this action level, to user\'s own data, unless user has permissions at or above this',
        'group_level' => 'Forbid this action to other families\' data, unless permitted',
        'menu_level' => 'Forbid users with group permissions below this from even being able to see the menu',
        'page' => 'The hacky old page this report is on, from the old system'
        );

	var $fb_requiredFields = array('report_name', 'realm_id', 'page');


}
