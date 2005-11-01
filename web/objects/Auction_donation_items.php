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
	var $fb_crossLinks = array(array('table' => 'auction_packages_join', 
									 'toTable' => 'packages',
									 'toField' => 'package_id',
									 'type' => 'select'),
                               array('table' => 'auction_items_families_join', 
									 'toTable' => 'families',
									 'toField' => 'auction_item_id',
									 'type' => 'select'));


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



// set item_description lines = 3
	
}
