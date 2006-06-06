<?php
/**
 * Table Definition for in_kind_donations
 */
require_once 'DB/DataObject.php';

class In_kind_donations extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'in_kind_donations';               // table name
    var $in_kind_donation_id;             // int(32)  not_null primary_key unique_key auto_increment
    var $item_description;                // blob(16777215)  blob
    var $quantity;                        // int(5)  
    var $item_value;                      // real(11)  
    var $date_received;                   // date(10)  binary
    var $school_year;                     // string(50)  
    var $thank_you_id;                    // int(32)  
    var $_cache_item_description;         // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('In_kind_donations',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_formHeaderText = 'Springfest In-Kind Donations';
	var $fb_fieldLabels = array(
		"quantity" => "Quantity of items", 
		"item_description" => "Description of item" ,
		'item_value' => 'Estimated TOTAL Value ($)' ,
		"date_received" => "Date Item received" ,
		"school_year" => "School Year" ,
		"in_kind_donation_id" => "Unique ID" ,
		"thank_you_id" => "Thank-You Sent" 
		);
	var $fb_linkDisplayFields = array('item_description');


    var $fb_shortHeader = 'In-kind Donations';

    var $fb_requiredFields = array(
        'quantity',
        'item_description',
        'item_value',
        'school_year'
        );

    var $fb_defaults = array(
        'quantity' => 1
        );

    var $fb_currencyFields = array(
        'item_value'
        );


    var $fb_sizes = array(
        'item_description' => 100
        );

// set item_description lines = 3
    

    // XXX WHACK THIS and use css and templates instead.
    // this should be deprecieated. because it sucks.
    // THIS NEEDS TO BE DONE IN PHPTAL!
    function public_donors(&$co, $sy)
        {
            $cp =& $co->page; // lazy
            $co->chosenSchoolYear = $sy; ///XXX nasty hack


            $res = '<div class="sponsor">';
            $res .= "<p><b>And Our Donors:</b></p>";
            $res .= "<ul>";


            $donors = $this->public_donors_structure(&$co);

            foreach($donors as $donor){
                $res .= $donor['url'] 
                    ? sprintf('<li><a href="%s">%s</a></li>', 
                              $donor['url'],
                              $donor['name'])
                    :sprintf("<li>%s</li>", 
                             $donor['name']);

            }
            $res .= "</ul></div><!-- end donor div -->";

            return $res;
        }



// XXX MORE! this also doesn't really belong in in_kind_donations either
    function public_donors_structure(&$co)
        {

            $companies =& new CoopObject(&$co->page, 'companies', &$nothing);
            $companies->obj->query(
                sprintf('select distinct companies.*
from companies
left join companies_auction_join 
on companies_auction_join.company_id = companies.company_id 
left join auction_donation_items 
on companies_auction_join.auction_donation_item_id = auction_donation_items.auction_donation_item_id
and auction_donation_items.school_year = "%s"
left join companies_income_join 
on companies_income_join.company_id = companies.company_id
left join income
on companies_income_join.income_id = income.income_id
and income.school_year = "%s"
left join companies_in_kind_join 
on companies_in_kind_join.company_id = companies.company_id
left join in_kind_donations
on companies_in_kind_join.in_kind_donation_id = in_kind_donations.in_kind_donation_id
and in_kind_donations.school_year = "%s"
left join sponsorships on sponsorships.company_id = companies.company_id
left join ads on ads.company_id = companies.company_id
where
(income.payment_amount > 0
or auction_donation_items.item_value > 0
or in_kind_donations.item_value > 0)
and ads.ad_id is null
and sponsorships.sponsorship_id is null
order by if(companies.listing is not null, companies.listing, companies.company_name), companies.last_name', 
                        $co->getChosenSchoolYear(),
                        $co->getChosenSchoolYear(),
                        $co->getChosenSchoolYear()));
            $res = array();
            $i = 0;
            while($companies->obj->fetch()){
                $res[$i]['name'] =  $companies->obj->listing? 
    $companies->obj->listing : 
    $companies->obj->company_name;
                $res[$i]['url'] = $companies->obj->url > '' ?
    $co->page->fixURL($companies->obj->url) : false;
                $i++;
            }

            $co->page->confessArray($res, 'donors structure', 4);
            return $res;
        }



}
