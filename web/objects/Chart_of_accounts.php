<?php
/**
 * Table Definition for chart_of_accounts
 */
require_once 'DB/DataObject.php';

class Chart_of_accounts extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'chart_of_accounts';               // table name
    var $account_number;                  // int(32)  not_null primary_key unique_key
    var $description;                     // string(255)  
    var $account_type;                    // string(7)  enum
    var $join_to_table;                   // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Chart_of_accounts',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('description');
	var $fb_enumFields = array ('account_type');
	var $fb_fieldLabels = array('account_number' => "Account Number",
								'description' => "Description",
								'account_type' => "unknown field",
								'join_to_table' => "Joins to these entities");

	// returns a popup constrained by its jointotables
	function constrainedAccountPopup($linktable)
		{
			$this->orderBy('description');
			$this->whereAdd("join_to_table like '%$linktable%'");
			$this->find();
			$options[] = '-- CHOOSE ONE --';
			while($this->fetch()){
				$options[$this->account_number] = $this->description;
			}
			$el =& HTML_QuickForm::createElement('select', 'account_number', 
												 $this->fb_fieldLabels['account_number'], 
												 &$options);

			return $el;
		}
}
