<?php
/**
 * Table Definition for table_permissions
 */
require_once 'DB/DataObject.php';

class Table_permissions extends CoopDBDO 
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
    var $year_level;                      // int(5)  

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
        'realm_id' => 'Data/Menu Realm',
        'user_level' => 'Forbid this action level, or any above, to user\'s own data, ever.',
        'group_level' => 'Forbid this action to other families\' data, unless permitted.',
        'menu_level' => 'Forbid users with group permissions below this from even being able to see the menu',
        'year_level' => 'Forbit users with menu perms below this from doing this accion to OLD (not this school year) data'

        );

	var $fb_requiredFields = array('table_name', 'realm_id');
    var $fb_displayCallbacks = array('table_name' => 'useLabel',
                                     'field_name' => 'useLabel');

    var $fb_linkDisplayFields =  array('realm_id', 'table_name', 'field_name');


    function preGenerateForm(&$form)
        {
			$el =& HTML_QuickForm::createElement(
                'select', 
                $form->CoopForm->prependTable('table_name'), 
                $this->fb_fieldLabels['table_name'], 
                $this->_useHumanTableNameSelect(&$this));
            $this->fb_preDefElements['table_name'] = $el;
            
        }

    // returns options suitable for a select
    function _useHumanTableNameSelect(&$obj)
        {
            $foo = $this->factory('table_permissions');
			$foo->query('show tables');
			$options[] = '-- CHOOSE ONE --';
            $thing='Tables_in_'.$obj->_database;
			while($foo->fetch()){
                $tab = $this->factory($foo->$thing);
				$options[$foo->$thing] = 
                    $tab->fb_formHeaderText ? $tab->fb_formHeaderText : 
                    ucwords(strtr($foo->$thing, '_', ' '));
			}
            // nice. asort sorts on the value not the index.
            asort($options);
			return $options;
        }

  function useLabel(&$co, $val, $key)
        {
            //WARNING this can really suck if you change table names!!
            // you must run the update if needed
            $sub =& $this->factory($this->table_name);
                
            if($key == 'table_name'){
                return $sub->fb_formHeaderText;
            }

            if($key == 'field_name'){
                return $sub->fb_fieldLabels[$val] ? 
                    $sub->fb_fieldLabels[$val] : $val;
            }
            
        }


    function fb_display_summary(&$co)
        {
            /// TODO: the simple summary of what i can do to thistable
            /// and.... who can do it if i cannot!
            
        }

    function FOOfb_display_details(&$co)
        {
            /// TODO: the showperms from coopview
            
        }
    

}
