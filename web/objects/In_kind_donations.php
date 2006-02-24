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
		"family_id" => "Co-Op Family",
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
   'family_id',
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

/// XXX NOTE THIS FUNCTION NEEDS TO BE REWRITTEN!
/// it does not use the proper format for inclusion here in the dataobject
/// it needs to also return a hashtable(array) which can then be formatted
/// by the caller in whatever CSS or javascript way is needed
// XXX MORE! this also doesn't really belong in in_kind_donations either
function public_donors(&$cp, $sy)
{
	$res .= '<div class="sponsor">';
	$res .= "<p><b>And Our Donors:</b></p>";
	$companies =& new CoopObject(&$cp, 'companies', &$nothing);
	$companies->obj->query(
"select distinct companies.*
from companies
left join companies_auction_join 
on companies_auction_join.company_id = companies.company_id 
left join auction_donation_items 
on companies_auction_join.auction_donation_item_id = auction_donation_items.auction_donation_item_id
and auction_donation_items.school_year = '$sy'
left join companies_income_join 
on companies_income_join.company_id = companies.company_id
left join income
on companies_income_join.income_id = income.income_id
and income.school_year = '$sy'
left join companies_in_kind_join 
on companies_in_kind_join.company_id = companies.company_id
left join in_kind_donations
on companies_in_kind_join.in_kind_donation_id = in_kind_donations.in_kind_donation_id
and in_kind_donations.school_year = '$sy'
left join sponsorships on sponsorships.company_id = companies.company_id
left join ads on ads.company_id = companies.company_id
where
(income.payment_amount > 0
or auction_donation_items.item_value > 0
or in_kind_donations.item_value > 0)
and ads.ad_id is null
and sponsorships.sponsorship_id is null
order by if(companies.listing is not null, companies.listing, companies.company_name), companies.last_name");
	$res .= "<ul>";
	while($companies->obj->fetch()){
		if($companies->obj->url > ''){
			$res .= sprintf('<li><a href="%s">%s</a></li>', 
							 $cp->fixURL($companies->obj->url),
							 $companies->obj->listing? $companies->obj->listing : $companies->obj->company_name);
		} else {
			$res .= sprintf("<li>%s</li>", 
                            $companies->obj->listing? $companies->obj->listing : $companies->obj->company_name);
		}
	}
	$res .= "</ul></div><!-- end ad div -->";

	return $res;
}


}
