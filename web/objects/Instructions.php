<?php
/**
 * Table Definition for instructions
 */
require_once 'CoopDBDO.php';

class Instructions extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'instructions';                    // table name
    var $instruction_id;                  // int(32)  not_null primary_key unique_key auto_increment
    var $table_name;                      // string(255)  
    var $action;                          // string(6)  enum
    var $instruction;                     // blob(16777215)  blob

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Instructions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_formHeaderText =  'User Instructions';
	var $fb_shortHeader =  'Instructions';
	var $fb_linkDisplayFields = array('table_name', 'action');
	var $fb_fieldLabels = array (
        'table_name' => 'Table',
        'action'  => 'Action the instructions apply to',
        'instruction' => 'Instructions for this action'
		);

    var $fb_displayCallbacks = array('table_name' => 'useLabel');

    // TODO: genericie this! it's used in table_permissions, and elsewhere!
    function preGenerateForm(&$form)
        {

            // TODO: heirselect!
            $foo = $this->factory('table_permissions');
			$foo->query('show tables');
			$options[] = '-- CHOOSE ONE --';
            confessObj($this, 'this');
            $thing='Tables_in_'.$this->_database;
			while($foo->fetch()){
                $tab = $this->factory($foo->$thing);
				$options[$foo->$thing] = 
                    $tab->fb_formHeaderText ? $tab->fb_formHeaderText : strtr(ucwords($foo->$thing), '_', ' ');
			}
			$el =& HTML_QuickForm::createElement(
                'select', 
                $form->CoopForm->prependTable('table_name'), 
                $this->fb_fieldLabels['table_name'], 
                &$options);
            $this->fb_preDefElements['table_name'] = $el;
			return $el;
            
        }

    // THIS TOO! duplicate of elsewhere
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


}

