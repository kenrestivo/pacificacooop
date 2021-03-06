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
    var $preDefOrder = array('company_id', 'income_id', 'family_id');

    function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'income', 
                                   &$co);
            $auc->constrainSchoolYear();
            $co->protectedJoin($auc);
            $companies =& new CoopObject(&$co->page, 'companies', 
                                   &$co);
            $co->protectedJoin($companies);

            /// XXX HACK! NEED THIS IF I LINK IN COMPANIES!!
            /// because companies have a family_id.. ambiguous!
            $co->obj->selectAdd('companies_income_join.family_id as family_id');

           // TODO: somehow make orderbylinkdisplay() recursive
            $co->obj->orderBy('companies.company_name, companies.last_name, income.check_date');
            $co->grouper();
		}

    function preGenerateForm(&$form)
        {
            // the more "modern" way to do pregenerate form
            $el =& $form->createElement(
                'customselect', 
                $form->CoopForm->prependTable('income_id'), false);

            $inc =& new CoopObject(&$form->CoopForm->page, 'income', 
                                   &$form->CoopForm);
            
            $inc->constrainSchoolYear();
            $coa =& new CoopObject(&$form->CoopForm->page, 
                                   'chart_of_accounts',
                                   &$inc);
            $inc->protectedJoin($coa);
            $inc->obj->whereAdd('chart_of_accounts.join_to_table like "%compan%"');

            $inc->obj->find(); // in lieu of coopform::findlinkoptions()!
            
            $el->setValue($this->income_id);


            $el->_parentForm =& $form;
            $el->prepare(&$inc);

            $this->fb_preDefElements['income_id'] =& $el;
            
        }

    function afterInsert(&$co)
        {
            return $this->_updateSponsors(&$co);
        }
    
    function afterUpdate(&$co)
        {
            return $this->_updateSponsors(&$co);
        }
    
    function _updateSponsors(&$co)
        {
            require_once('COOP/Sponsorship.php');
            $sp = new Sponsorship(&$co->page, $this->school_year);
            $sp->updateSponsorships($this->company_id, 'company_id');
        }

}
