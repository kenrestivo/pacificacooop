<?php
/**
 * Table Definition for invitation_rsvps
 */
require_once 'DB/DataObject.php';

class Leads_income_join extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'leads_income_join';               // table name
    var $leads_income_join_id;            // int(32)  not_null primary_key unique_key auto_increment
    var $income_id;                       // int(32)  
    var $lead_id;                         // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Leads_income_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_fieldLabels = array(
        'income_id' => "Payment Information",
		'lead_id' => 'Invitee',
		);

	var $fb_formHeaderText = "Springfest Donations from Invitations";

	var $fb_linkDisplayFields = array('lead_id', 'income_id');

    var $fb_shortHeader = 'RSVPs';
 
    var $fb_joinPaths = array('school_year' => 'income',
                              'family_id' => 'leads:invitations');


    function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'income', 
                                   &$co);
            $co->protectedJoin($auc);

            $inv =& new CoopObject(&$co->page, 'invitations', 
                                   &$co);

            $leads =& new CoopObject(&$co->page, 'leads', 
                                   &$co);
            $leads->protectedJoin($inv);

            $co->protectedJoin($leads);

            $co->constrainSchoolYear();

            $co->constrainFamily();
           // TODO: somehow make orderbylinkdisplay() recursive
            $co->obj->orderBy('leads.last_name, leads.first_name,  leads.company, income.check_date');
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
            $inc->obj->whereAdd('chart_of_accounts.join_to_table like "%leads%"');

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
            require_once('Sponsorship.php');
            $sp = new Sponsorship(&$co->page, $this->school_year);
            $sp->updateSponsorships($this->lead_id, 'lead_id');
        }


    function postGenerateForm(&$form)
        {
            $el =& $form->getElement(
                $form->CoopForm->prependTable('lead_id'));
            $el->searchByID =  'RSVP Code';
        }



    function fb_display_view(&$co)
        {
            return $this->swapView(&$co) . $co->simpleTable(true,true);
        }

    function beforeForm(&$co)
        {
            return $this->swapView(&$co);
        }


    function swapView(&$co)
        {
            return $co->page->selfURL(
                array('value' => 'Switch to TICKETS instead of random donations',
                      'inside' => array(
                          'action' => $co->page->vars['last']['action'],
                          'table' => 'tickets' )));
        }

}
