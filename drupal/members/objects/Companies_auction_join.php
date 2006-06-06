<?php
/**
 * Table Definition for companies_auction_join
 */
require_once 'DB/DataObject.php';

class Companies_auction_join extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'companies_auction_join';          // table name
    var $companies_auction_join_id;       // int(32)  not_null primary_key unique_key auto_increment
    var $auction_donation_item_id;        // int(32)  
    var $company_id;                      // int(32)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Companies_auction_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('auction_donation_item_id', 
									  'company_id');

	var $fb_fieldLabels = array ('auction_donation_item_id' => 'Auction Item',
                                 'company_id' => 'Solicitation Company',
                                 'family_id' => 'Soliciting Family');

	var $fb_formHeaderText =  'Springfest Solicitation Auction Donation Items';

	var $fb_requiredFields = array('auction_donation_item_id', 
                                   'company_id',
                                   'family_id');


    var $fb_shortHeader = 'Solicitation Auction';

    var $fb_putNewFirst = array ('auction_donation_item_id');

    function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'auction_donation_items', 
                                   &$co);
            $auc->constrainSchoolYear();
            $auc->constrainFamily();
            $co->protectedJoin($auc);
            // TODO: somehow make orderbylinkdisplay() recursive
            $this->orderBy('short_description, item_description');
            $co->grouper();
		}



}
