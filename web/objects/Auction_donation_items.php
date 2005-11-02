<?php
/**
 * Table Definition for auction_donation_items
 */
require_once 'DB/DataObject.php';

class Auction_donation_items extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'auction_donation_items';          // table name
    var $auction_donation_item_id;        // int(32)  not_null primary_key unique_key auto_increment
    var $item_description;                // blob(16777215)  blob
    var $item_value;                      // real(11)  
    var $date_received;                   // date(10)  binary
    var $location_in_garage;              // string(255)  
    var $quantity;                        // int(5)  
    var $item_type;                       // string(16)  enum
    var $school_year;                     // string(50)  
    var $committed;                       // string(3)  enum
    var $thank_you_id;                    // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Auction_donation_items',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('item_description');
	var $fb_selectAddEmpty = array ('package_id');
	var $fb_enumFields = array ('item_type');
	var $fb_textFields = array ('item_description'); 

	var $fb_fieldLabels = array(
		"quantity" => "Quantity of items", 
		"item_description" => "Description of item" ,
		'item_value' => 'Estimated TOTAL Value ($)' ,
		"item_type" => "Physical Product or Gift Certificate",
		"date_received" => "Date Item received" ,
		"location_in_garage" => "Where It's Located" ,
		"school_year" => "School Year" ,
		"auction_donation_item_id" => "Unique ID" ,
		"thank_you_id" => "Thank-You Sent" 
		);

	var $fb_formHeaderText =  'Springfest Auction Donation Items';

// XXX these appear to be broken! go fix.
// 	var $fb_crossLinks = array(array('table' => 'auction_items_families_join', 
// 									 'toTable' => 'families',
// 									 'toField' => 'auction_item_id',
// 									 'type' => 'select'),
//                                array('table' => 'auction_packages_join', 
// 									 'toTable' => 'packages',
// 									 'toField' => 'package_id',
// 									 'type' => 'select'));


	var $fb_requiredFields = array('item_description', 'quantity', 
								   'school_year',  'item_value', 
								   'item_type', 'school_year');


    var $fb_shortHeader = 'Donation Items';

    var $fb_defaults = array(
        'quantity' => 1
        );
    
    var $fb_dupeIgnore = array(
        'item_value',
        'date_received',
        'quantity',
        'location_in_garage',
        'thank_you_id'
        );

    var $fb_currencyFields = array(
        'item_value'
        );

    var $fb_sizes = array(
        'item_description' => 100
        );

    // XXX ACK! multiple paths.
    // i need some way to decide, based on previous table!
    var $fb_joinPaths = array('family_id' => 'auction_items_families_join');

	function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'auction_items_families_join', 
                                       &$co);
            $co->constrainSchoolYear();
            $co->constrainFamily();
            $co->protectedJoin($auc);
            $co->orderByLinkDisplay();

		}

	// form that blasts over to the packages::new, to create a new one
	// just generates a CREATE NEW button with all the shit inside
	function newPackageForm(&$co)
		{
			$form =& new HTML_QuickForm('newpackageform', 'post', 
										'packages.php');


			$form->addElement('header', 'newpackageheader', 
							  'Create a new Package starting with this Auction Item?');
				 // donated by! first guess families...
			//$co->debugWrap(2);
			$aifj =& new CoopObject(&$co->page, 
									'auction_items_families_join', &$top);
			$aifj->obj->auction_donation_item_id = 
				$this->auction_donation_item_id; 
			$fam =& new CoopObject(&$co->page, 'families', &$aifj);
			$fam->obj->joinAdd($aifj->obj);
			if($fam->obj->find(true)){
				$donatedby = $fam->obj->name . " Family";
			}
			
			// now guess companies. blah
			$caj =& new CoopObject(&$co->page, 
								   'companies_auction_join', &$top);
			$caj->obj->auction_donation_item_id = 
				$this->auction_donation_item_id; 
			$co =& new CoopObject(&$co->page, 'companies', &$caj);
			$co->obj->joinAdd($caj->obj);
			if($co->obj->find(true)){
				$donatedby = $co->obj->company_name;
			}
			
			$prefix = 'packages-';
			foreach(array(
						$prefix . 'package_type' => 'Silent',
						$prefix . 'item_type' => $this->item_type,
						$prefix . 'package_description' => 
						$this->item_description,
						$prefix . 'donated_by_text' => $donatedby,
						$prefix . 'package_value' => $this->item_value,
						$prefix . 'bid_increment' => 
						ceil($this->item_value / 10),
						$prefix . 'starting_bid' => ceil($this->item_value /2),
						$prefix . 'school_year' => $this->school_year,
						$prefix . 'auction_donation_item_id[]' => 
						$this->auction_donation_item_id,
						'action' => 'add' // need this for legacy form
						) as $key => $val)
			{
				$form->addElement('hidden', $key, $val);
			}

			// legacy
			if($sid = thruAuthCore($co->page->auth)){
				$form->addElement('hidden', 'coop', $sid); 
			}

			$form->addElement('submit', null, 'Make New Package');

			return $form;
		}// end newpackageform


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
            $form->addElement('hidden', 'amount', '50.00');
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
            $tab->addCol(array('<p>Or you may click here to pay a $50 fee instead of donating an auction item:</p>'));
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
            $auc = new CoopObject(&$co->page, $co->table, &$co);
            $auc->obj->query(sprintf("
	select  sum(auction_donation_items.item_value) as total_value
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
                             sprintf("Congratulations! You have donated %d item%s worth $%0.02f.  
				You're welcome to enter more below if you wish.", 
                                     $count, 
                                     $count == 1 ? '' : 's',
                                     $auc->obj->total_value));
            }

            // XXX total value, not quantity
            $res .= sprintf("You have donated %d item%s worth $%0.02f thus far. 
				You must enter $%0.02f worth before %s.",
                            $count, 
                            $count == 1 ? "" : "s",
                            $auc->obj->total_value,
                            COOP_AUCTION_VALUE_REQUIRED - $auc->obj->total_value,
                            timestamp_db_php($ev->event_date)
                );
            return array(false, $res);
        }



// set item_description lines = 3
	
}
