<?php 

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once("utils.inc");
require_once('CoopPage.php');
require_once('CoopView.php');

$debug = 0;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();

$sy = findSchoolYear();

$top = new CoopView(&$cp, 'companies');


$top->obj->query("
select * from companies         
       left join companies_income_join 
               on companies.company_id = 
                   companies_income_join.company_id
			left join income
					on income.income_id = companies_income_join.income_id
	left join companies_auction_join
			on companies_auction_join.company_id = companies.company_id
			left join auction_donation_items
					on auction_donation_items.auction_donation_item_id =
							auction_donation_items.auction_donation_item_id
	where (income.income_id is not null and income.school_year = '$sy')
			or (auction_donation_items.auction_donation_item_id is not null  
							and auction_donation_items.school_year = '$sy')
");

print $top->simpleTable(false);
	


done ();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->





