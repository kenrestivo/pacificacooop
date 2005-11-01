<?php
/**
 * Table Definition for auction_items_families_join
 */
require_once 'DB/DataObject.php';

class Auction_items_families_join extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'auction_items_families_join';     // table name
    var $auction_items_families_join_id;    // int(32)  not_null primary_key unique_key auto_increment
    var $auction_donation_item_id;        // int(32)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Auction_items_families_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('auction_donation_item_id', 
									  'family_id');

	var $fb_fieldLabels = array ('auction_donation_item_id' => 'Auction Item',
                                 'family_id' => 'Co-Op Family');

	var $fb_formHeaderText =  'Springfest Family Auction Donation Items';

	var $fb_requiredFields = array('auction_donation_item_id', 
                                   'family_id');


    var $fb_shortHeader = 'Family Donations';

    function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'auction_donation_items', 
                                   &$co);
            $auc->constrainSchoolYear();
            $auc->constrainSchoolYear();
            $co->protectedJoin($auc);
            //$this->orderBy()
            
		}



}
