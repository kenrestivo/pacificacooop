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
    var $short_description;               // string(255)  
    var $_cache_item_description;         // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Auction_donation_items',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('short_description');
	var $fb_selectAddEmpty = array ('package_id');
	var $fb_enumFields = array ('item_type');
	var $fb_textFields = array ('item_description'); 

	var $fb_fieldLabels = array(
		"quantity" => "Quantity of items", 
		"short_description" => "Short description of item" ,
		"item_description" => "Long, Detailed Description of item" ,
		'item_value' => 'Estimated TOTAL Value ($)' ,
		"item_type" => "Physical Product or Gift Certificate",
		"date_received" => "Date Item Received" ,
		"location_in_garage" => "Where It's Located" ,
		"school_year" => "School Year" ,
		"auction_donation_item_id" => "Unique ID" ,
		"thank_you_id" => "Thank-You Sent" 
		);

	var $fb_formHeaderText =  'Springfest Auction Donation Items';


	var $fb_crossLinks = array(
// XXX this appears to be broken! go fix.
// array('table' => 'auction_items_families_join', 
// 									 'toTable' => 'families',
// 									 'toField' => 'family_id',
// 									 'type' => 'select'),
                               array('table' => 'auction_packages_join', 
									 'toTable' => 'packages',
									 'toField' => 'package_id',
									 'type' => 'select'));


	var $fb_requiredFields = array('short_description',
                                   'item_description', 'quantity', 
								   'school_year',  
                                    // priceless! 'item_value', 
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
        'short_description' => 50,
        'item_description' => 100
        );

    
    var $fb_joinPaths = array('family_id' => array('auction_items_families_join',
                                                   'companies_auction_join'));

    var $fb_extraDetails = array('auction_packages_join:packages');

	function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'auction_items_families_join', 
                                       &$co);


            $fam =& new CoopObject(&$co->page, 'families', &$co);

            $auc->protectedJoin($fam);
            $co->protectedJoin($auc);

            //and go get the donor

            //  add company join too, so i knwo who donated
            $caj =& new CoopObject(&$co->page, 'companies_auction_join', 
                                   &$co);
            $companies =& new CoopObject(&$co->page, 'companies', 
                                   &$co);
            $caj->protectedJoin($companies);

            $co->protectedJoin($caj);
            
//             XXX HACK! NEED THIS IF I LINK IN COMPANIES!!
//             because companies have a family_id.. ambiguous!

            $this->selectAdd('auction_items_families_join.family_id as family_id');

            $co->constrainSchoolYear();
            $co->constrainFamily();

            $co->orderByLinkDisplay();
            $co->grouper();
		}


    function postGenerateForm(&$form)
        {

// TODO: wrap this in an ispermittedfield, whack it for anyone with group edit
//                             'Need more description, please make it sound attractive to potential buyers. At least 40 characters long.', 
//                             'minlength', 40, 'client');


//              $form->addRule($form->CoopForm->prependTable('item_description'), 
//                             'Do not enter "cash donation" here. Simply write your check and hand it to the Springfest Coordinators, and you will be removed from this list.', 'regex', '/.*?(?!cash).*?/i', 'client');
//              $form->addRule($form->CoopForm->prependTable('item_description'), 
        }

    function afterForm(&$co)
        {
            $co->page->printDebug('after form', 3);
            if($co->id > 0){
                $pkgfake =& new CoopObject(&$co->page, 'packages', &$co);
                if($pkgfake->isPermittedField(null, null, true) >= ACCESS_ADD){
                    $npf =& $this->newPackageForm(&$co);
                    return $npf->toHTML();
                }
            }
        }


	// form that blasts over to the packages::new, to create a new one
	// just generates a CREATE NEW button with all the shit inside
	// XXXX THIS IS a NASTY HACK that assumes dicking around with
	// the internals of newcoopdispatcher variables. uglay!
	function newPackageForm(&$co)
		{
			$form =& new HTML_QuickForm(
                'newpackageform', 'post', 
                COOP_GENERIC_TABLE_ENGINE_ABSOLUTE_URL_PATH);
            $form->removeAttribute('name');


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
						$prefix . 'package_type_id' => COOP_PACKAGE_TYPE_SILENT,
						$prefix . 'item_type' => $this->item_type,
						$prefix . 'package_description' => 
						$this->item_description,
						$prefix . 'package_title' => $this->short_description,
						$prefix . 'donated_by_text' => $donatedby,
						$prefix . 'package_value' => $this->item_value,
						$prefix . 'bid_increment' => 
						round(ceil($this->item_value / 
                                   COOP_DEFAULT_BID_INCREMENT_DIVISOR) / 
                              COOP_DEFAULT_BID_INCREMENT_CLAMP) 
                        * COOP_DEFAULT_BID_INCREMENT_CLAMP,
						$prefix . 'starting_bid' => 
                        round(ceil($this->item_value / 
                                   COOP_DEFAULT_STARTING_BID_DIVISOR) / 
                              COOP_DEFAULT_STARTING_BID_CLAMP) 
                        * COOP_DEFAULT_STARTING_BID_CLAMP,
						$prefix . 'school_year' => $this->school_year,
						$prefix . 'auction_donation_item_id[]' => 
						$this->auction_donation_item_id,
						'action' => 'add', // need this for newdispatcher
						'table' => 'packages' // need this for newdispatcher
						) as $key => $val)
			{
				$form->addElement('hidden', $key, $val);
			}

            $form->addElement('hidden', 'push', $co->prependTable($co->pk));

			// legacy
			if($sid = thruAuthCore($co->page->auth)){
				$form->addElement('hidden', 'coop', $sid); 
			}

			$form->addElement('submit', null, 'Make New Package');

			return $form;
		}// end newpackageform

    function beforeForm(&$co)
        {

            $inner = 'foo bar baz';

            $res = "";

            $pkgs = new CoopView(&$co->page, 'packages', &$co);
            $pkgs->obj->whereAdd(sprintf('%s.package_type_id = %d', 
                                         $pkgs->table,
                                         COOP_PACKAGE_TYPE_LIVE));
            
            $tab= new HTML_Table();
			$tab->altRowAttributes(1, 'class="altrow1"', 
								   'class="altrow2"');
            $tab->addRow(array($pkgs->obj->fb_fieldLabels['package_title'],
                               $pkgs->obj->fb_fieldLabels['package_description']),
                         'class="tableheaders"', 'TH');
            $pkgs->find(true);
            while($pkgs->obj->fetch()){
                $tab->addRow(array($pkgs->obj->package_title, 
                                   $pkgs->obj->package_description));
            }

            $res .= '<h4>Try to donate items which will fit into one of the "baskets" listed here:</h4>';
            $res .= '<div class="possible_packages_scroll"><div class="possible_packages">'. $tab->toHTML() . '</div></div>';
            return $res;
        }



    function fb_display_view(&$co)
        {
            $co->schoolYearChooser();
            $aphtml = "";
            // FAMILY AND ALPHABETIC CHOOSERS
            if($co->isPermittedField() >= ACCESS_VIEW){
                /////////////// RECEIVED ON
                // now the nightmare received on
                $printed_dates['%'] ='ALL';
                $dates =& new CoopView(&$co->page, $this->__table, &$co);
                $dates->obj->query(
                    sprintf(
                        'select  date_received, 
date_format(date_received, "%%a %%m/%%d/%%Y") 
                        as received_human ,
count(auction_donation_item_id) as count
from %s where school_year = "%s" group by date_received order by date_received', 
                        $this->__table,
                        $co->getChosenSchoolYear()));
                while($dates->obj->fetch()){
                    $printed_dates[$dates->obj->date_received] = 
                        sprintf('%s (%2d items)', 
                                $dates->obj->received_human,
                                $dates->obj->count);
                }
            
                // do this AFTER the query, so that NOT YET
                // gets assigned to the null value the query returns
                $printed_dates[''] ='NOT YET';
            
                $datessel =& $co->searchForm->addElement(
                    'select', 
                    'date_received', 
                    $this->fb_fieldLabels['date_received'], 
                    $printed_dates,
                    array('onchange' =>
                          'this.form.submit()'));
                        
            
                $co->searchForm->setDefaults(
                    // NOTE! isset not empty! preserve nulls!
                    array('date_received' => 
                          isset($co->page->vars['last']['date_received']) ? $co->page->vars['last']['date_received'] : '%'));
            
                $bar = $datessel->getValue();
                $date_received = $bar[0];
                $co->page->vars['last']['date_received'] = $date_received;
            
                switch($date_received){
                case '%':
                    // don't constrain at all by labelprinted, show 'em all!
                    break;
                case '':
                    // nothing. only the nulls
                    $this->whereAdd(
                        '(date_received is null or date_received < "1000-01-01")'); 
                    break;
                default:
                    // there is a valid (i hope) date in there, show it
                    $this->whereAdd(sprintf('%s.date_received = "%s"', 
                                            $co->table,
                                            $date_received));
                }





                ////////package stuff
                $haspackage =& $co->searchForm->addElement(
                    'select', 
                    'has_package', 
                    'Is Part of a Package?', 
                    array('%' => 'ALL',
                          'yes'=>'Belongs to a Package', 
                          'no' => 'Orphan (No package)'),
                    array('onchange' =>
                          'this.form.submit()')) ; 


                $co->searchForm->setDefaults(
                    // NOTE! isset not empty! preserve nulls!
                    array('has_package' => 
                          isset($co->page->vars['last']['has_package']) ? $co->page->vars['last']['has_package'] : '%'));


                $foo = $haspackage->getValue();
                $has_package = $foo[0];
                $co->page->vars['last']['has_package'] = $has_package;

                switch($has_package){
                case 'yes':
                    $co->obj->whereAdd('(auction_packages_join.package_id is not null and auction_packages_join.package_id > 0)');
                    break;
                case 'no':
                    $co->obj->whereAdd('(auction_packages_join.package_id is null or auction_packages_join.package_id < 0)');
                    break;
                case '%':
                default:
                    break;
                }



                //////////////////////////////////////
                // last but not least, my counts and pagers
                $co->showChooser = 1;

                
                // FINALLY crap for my choosers
                // by default, no change button!
                $co->searchForm->addElement('submit', 'savebutton', 'Change');
                
            } 

            $co->linkConstraints();
            

            //one more thing to add
            $apj =& new CoopView(&$co->page, 'auction_packages_join', &$co);
            $co->protectedJoin($apj);
            

            return $co->simpleTable(true,true);
        }



// set item_description lines = 3
	
}
