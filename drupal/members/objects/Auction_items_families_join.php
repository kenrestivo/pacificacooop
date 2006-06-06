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
    var $fb_putNewFirst = array ('auction_donation_item_id');

    function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'auction_donation_items', 
                                   &$co);
            $auc->constrainSchoolYear();
            $co->protectedJoin($auc);
            // TODO: somehow make orderbylinkdisplay() recursive
            $this->orderBy('item_description');
            $co->grouper();
		}


    function fb_display_alert(&$co)
        {


            list($ok, $res) = $this->_alert_or_status(&$co);
            
            if($ok){
                return '';
            }
            
            return $res; //XXX temporary hack till i get sandbox first. 

            require_once('paypal.php');

            //PAYPAL BUTTON HERE
            $form =& new paypalForm('Springfest Invitations Forfeit Fee', 
                                    'auctionforfeit',   'MembersPage', false);
            $form->addElement('hidden', 'amount', COOP_AUCTION_VALUE_REQUIRED);
            $form->addElement('hidden', 'custom', 
                              sprintf("fid%d:coa%d",
                                      $id, COOP_AUCTION_FORFEIT_FEE)); 
            $form->addElement('submit', NULL, 
                              "Pay Auction Fee");
            //fix elements
            $form->addElement("hidden", "cancel", $_SESSION['PHP_SELF']);
            $form->addElement("hidden", "item_number", "Family Fees");


            //XXXX do auto-processing before sending them back
            //$this->addElement("hidden", "return", $_SESSION['PHP_SELF']);
		
            //TODO: use css
            $tab = new HTML_Table();
            $tab->addCol(
                array(
                    sprintf('<p>Or you may click here to pay a $%0.2f fee instead of donating an auction item:</p>', 
                            COOP_AUCTION_FORFEIT_FEE)));
            $tab->addCol(array($form->toHTML()));
            
            $res .= $tab->toHTML();

            return $res;
            
        }


    function fb_display_summary(&$co)
        {
            list($ok, $res) = $this->_alert_or_status(&$co);

            // NOTE: i can't include the summary here because schoolyearchooser

            if($ok){
                return $res;
            }

        }


    function _alert_or_status(&$co)
        {
            $res = '';

            $fid = $co->page->userStruct['family_id'];

            if(!$fid){
                return array(true, ''); // give up, it's a teacher
            }



            // this code ought to be taken out and shot
            $cv = new CoopObject(&$co->page, 'families_income_join', $nothing);
            $cv->obj->family_id = $fid;
            $inc = new CoopObject(&$co->page, 'income', $nothing);
            $inc->obj->school_year = $co->page->currentSchoolYear;
            $inc->obj->account_number = COOP_AUCTION_FORFEIT_FEE; 
            $cv->protectedJoin($inc);
            $found = $cv->obj->find(true);
            
            // TODO: check amount is correct. duh.
            if($found){  
                $cv->obj->getLinks();
                return array(true, 
                             sprintf("Congratulations! 
						You have paid your forfeit fee of $%0.2f . 
						You don't need to enter any names this year.", 
                                    $cv->obj->_income_id->payment_amount));
            }

			// check for indulgences
            $cv = new CoopObject(&$co->page, 'nag_indulgences', $nothing);
            $cv->obj->family_id = $fid;
            $cv->obj->school_year = $co->page->currentSchoolYear;
            $cv->obj->whereAdd('(indulgence_type = "Family Auctions" or indulgence_type = "Everything")');
            $found = $cv->obj->find(true);
		
//		confessObj($cv);
            if($found){  
                $cv->obj->getLinks();
                return array(
                    true,
                    sprintf("You were granted a special Indulgence on %s (%s).
						You don't need to enter any names this year.", 
                            sql_to_human_date($cv->obj->granted_date), 
                            $cv->obj->note));
            }
	
            $ev = $this->factory('calendar_events');
            $ev->event_id = COOP_AUCTION_DUE_EVENT;
            $ev->school_year = $co->page->currentSchoolYear;
            if($ev->find(true) < 1){
                $co->page->yearNotSetupYet();
            }

            // how much we got?
            $auc = new CoopObject(&$co->page, 'auction_donation_items', &$co);
            $auc->obj->query(sprintf("
	select  sum(auction_donation_items.item_value) as total_value,
        count(auction_donation_items.auction_donation_item_id) as auc_count
		from auction_donation_items
			left join auction_items_families_join on auction_donation_items.auction_donation_item_id = auction_items_families_join.auction_donation_item_id
			left join companies_auction_join 
				on auction_donation_items.auction_donation_item_id = companies_auction_join.auction_donation_item_id
			left join families 
				on coalesce(auction_items_families_join.family_id, companies_auction_join.family_id) =
				families.family_id
		where families.family_id = %d
				and school_year = '%s'
		group by families.family_id
		", 
                                     $fid, 
                                     $co->page->currentSchoolYear));
            $auc->obj->fetch();

            if($auc->obj->total_value >= COOP_AUCTION_VALUE_REQUIRED){
                return array(true, 
                             sprintf("Congratulations! You have committed to donate %d item%s worth $%0.02f.  
				You're welcome to donate more auction items below if you wish.", 
                                     $auc->obj->auc_count, 
                                     $auc->obj->auc_count == 1 ? '' : 's',
                                     $auc->obj->total_value));
            }

            // XXX total value, not quantity
            if($auc->obj->auc_count > 0){
                $res .= sprintf(
                    'You have committed to donate %d item%s worth $%0.02f thus far.', 
                    $auc->obj->auc_count,       
                    $auc->obj->auc_count == 1 ? "" : "s",
                    $auc->obj->total_value); 
            }
            $res .= sprintf(' You must commit to donating%s item(s) worth $%0.02f or more before %s.',
                            $auc->obj->auc_count ? ' additional' : '',
                            COOP_AUCTION_VALUE_REQUIRED - $auc->obj->total_value,
                            timestamp_db_php($ev->event_date)
                );
            return array(false, $res);
        }



}
