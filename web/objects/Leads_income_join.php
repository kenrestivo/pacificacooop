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
	var $fb_fieldsToRender = array ('lead_id', 'income_id');
	var $fb_formHeaderText = "Springfest Donations from Invitations";

	var $fb_linkDisplayFields = array('lead_id', 'income_id');

    var $fb_shortHeader = 'RSVPs';
 
    var $fb_joinPaths = array('school_year' => 'income',
                              'family_id' => 'leads:invitations');


    function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'income', 
                                   &$co);
            $auc->constrainSchoolYear();
            $co->protectedJoin($auc);
            $leads =& new CoopObject(&$co->page, 'leads', 
                                   &$co);
            $co->protectedJoin($leads);

           // TODO: somehow make orderbylinkdisplay() recursive
            $co->obj->orderBy('leads.last_name, leads.first_name,  leads.company, income.check_date');
            $co->grouper();
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



}
