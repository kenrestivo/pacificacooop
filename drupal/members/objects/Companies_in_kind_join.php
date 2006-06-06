<?php
/**
 * Table Definition for companies_in_kind_join
 */
require_once 'DB/DataObject.php';

class Companies_in_kind_join extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'companies_in_kind_join';          // table name
    var $companies_in_kind_join_id;       // int(32)  not_null primary_key unique_key auto_increment
    var $in_kind_donation_id;             // int(32)  
    var $company_id;                      // int(32)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Companies_in_kind_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('company_id', 'in_kind_donation_id');

	var $fb_fieldLabels = array ('in_kind_donation_id' => 'Donation',
                                 'company_id' => 'Solicitation Company',
                                 'family_id' => 'Soliciting Family');

	var $fb_formHeaderText =  'Springfest Solicitation In-Kind Donations';

	var $fb_requiredFields = array('in_kind_donation_id',
                                   'family_id',
                                   'company_id');


    var $fb_shortHeader = 'Solicitation In-Kind';
    var $preDefOrder = array('company_id', 'in_kind_donation_id', 'family_id');

    function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'in_kind_donations', 
                                   &$co);
            $auc->constrainSchoolYear();
            $co->protectedJoin($auc);
            $companies =& new CoopObject(&$co->page, 'companies', 
                                   &$co);
            $co->protectedJoin($companies);

            /// XXX HACK! NEED THIS IF I LINK IN COMPANIES!!
            /// because companies have a family_id.. ambiguous!
            $co->obj->selectAdd('companies_in_kind_join.family_id as family_id');

           // TODO: somehow make orderbylinkdisplay() recursive
            $co->obj->orderBy('companies.company_name, companies.last_name, in_kind_donations.item_description');
            $co->grouper();
		}



}
