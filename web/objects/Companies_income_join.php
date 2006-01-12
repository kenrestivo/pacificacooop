<?php
/**
 * Table Definition for companies_income_join
 */
require_once 'DB/DataObject.php';

class Companies_income_join extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'companies_income_join';           // table name
    var $companies_income_join_id;        // int(32)  not_null primary_key unique_key auto_increment
    var $income_id;                       // int(32)  
    var $company_id;                      // int(32)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Companies_income_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('company_id', 'income_id');

	var $fb_fieldLabels = array ('income_id' => 'Payment Information',
                                 'company_id' => 'Solicitation Company',
                                 'family_id' => 'Soliciting Family');

	var $fb_formHeaderText =  'Springfest Solicitation Cash Donations';

	var $fb_requiredFields = array('income_id',
                                   'family_id',
                                   'company_id');


    var $fb_shortHeader = 'Solicitation Cash';

    function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'income', 
                                   &$co);
            $auc->constrainSchoolYear();
            $co->protectedJoin($auc);
            // TODO: somehow make orderbylinkdisplay() recursive
            $co->grouper();
		}

        function preGenerateForm(&$form)
        {

            $el =& $form->addElement(
                'customselect', 
                $form->CoopForm->prependTable('income_id'), false);

            $inc =& new CoopObject(&$form->CoopForm->page, 'income', 
                                   &$form->CoopForm);
            $opts = $inc->getLinkOptions(true, true);
            
            $el->_parentForm =& $form;
            $el->prepare();

            $this->fb_preDefElements[$form->CoopForm->prependTable('income_id')] = $el;
			return $el;
            
        }



}
