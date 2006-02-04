<?php

//  Copyright (C) 2003-2005  ken restivo <ken@restivo.org>
// 
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
// 
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details. 
// 
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

//$Id$

require_once('CoopPage.php');
require_once('CoopView.php');
require_once('HTML/Table.php');
require_once('HTML/QuickForm.php');


/// XXX duplicate of what's in solicit summary
function schoolYearChooser(&$cp, $table)
{
    $res ='';
    
    $at = new CoopView(&$cp, $table, $none);
 
    $syform =& new HTML_QuickForm('schoolyearchooser', false, false, 
                                  false, false, true);
    $syform->removeAttribute('name');
    $el =& $syform->addElement('select', 'gschoolyear', 'Choose School Year', 
                               //TODO check ispermittedfield for allyears!
                               $at->getSchoolYears(null, true),
                               array('onchange' =>'this.form.submit()'));

    if($sid = thruAuthCore($at->page->auth)){
        $syform->addElement('hidden', 'coop', $sid); 
    }

    $syform->setDefaults(array('gschoolyear' => $at->page->currentSchoolYear));

    $res .= $syform->toHTML();
    
    $foo = $el->getValue();
    $schoolyear=$foo[0];

    return array($schoolyear, $res);
}





function viewHack(&$cp)
{
    $res = '';

    $res .='This is the famous "Carrie Report". 	
	    It shows the summary of all income and general 
		performance statistics for the entire program.';


    list($schoolyear, $chooser) = schoolYearChooser(&$cp, 'income');



    $res .= $chooser; // hack

	
	$res .= "<h2>Totals for $schoolyear </h2>";

/* familid's in 
       raffle_income_join
       families_income_join
       companies
*/



	$res .= showRawQuery("Total ALL income from ALL sources", 
					   "select chart_of_accounts.description as Description, 
	   sum(payment_amount)  as Total 
		from income 
						   left join chart_of_accounts 
						on income.account_number = 
								chart_of_accounts.account_number 
   where income.school_year = \"$schoolyear\"
		group by income.account_number order by total desc", 1
		);


	$res .= showRawQuery("Total ALL income by reconciliation status", 
				 "select chart_of_accounts.description, 
	   sum(if(income.cleared_date>0,0,income.payment_amount)) 
			as not_cleared_yet ,
	   sum(if(income.cleared_date>0,income.payment_amount,0)) 
			as cleared ,
	   sum(payment_amount)  as total 
		from income 
			   left join chart_of_accounts 
						on income.account_number = 
								chart_of_accounts.account_number 
				where income.school_year = \"$schoolyear\"
		group by income.account_number order by total desc", 1
		);



	$res .= showRawQuery("Income By Date",
		 "select concat(monthname(check_date), ' ', year(check_date)) 
				as Month, 
				sum(payment_amount) as Total 
				from income 
				where school_year = '$schoolyear' 
				group by Month 
				order by check_date" , 1);


	$res .= "<h2>Solicitation</h2>";


	// fix an dptu itn

	$res .= showRawQuery("Solicitation Cash Income by Type",
" 
select coa.description as Description,
        sum(inc.total) as Before_Event, 
        sum(pur.total) as At_Event
from chart_of_accounts as coa
left join 
    (select account_number, sum(payment_amount) as total
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '$schoolyear'
        group by cinj.company_id) 
    as inc
        on inc.account_number = coa.account_number
left join 
    (select  account_number, payment_amount as total
     from springfest_attendees as atd
    left join auction_purchases  as ap
            on ap.springfest_attendee_id = 
                atd.springfest_attendee_id
     left join income 
              on ap.income_id = 
                income.income_id
        where income.school_year = '$schoolyear'
        group by atd.company_id) 
    as pur
        on pur.account_number = coa.account_number
group by coa.account_number
having Before_Event >0 or At_Event > 0
order by Before_Event desc, At_Event desc
"
					   ,1);

	$res .= "<h2>Auction Donations</h2>";
	$res .= showRawQuery("Auction Donations by Type", 
"
select 'Auction Donation Items', sum(if(auction_items_families_join.auction_donation_item_id is null, 0, 
item_value)) as Family_Auction, 
sum(if(companies_auction_join.auction_donation_item_id is null, 0, 
item_value)) as Solicitation_Auction, sum(item_value) as Total_Auction
from auction_donation_items
left join companies_auction_join
on companies_auction_join.auction_donation_item_id = 
auction_donation_items.auction_donation_item_id
left join auction_items_families_join
on auction_items_families_join.auction_donation_item_id = 
auction_donation_items.auction_donation_item_id
where school_year = '$schoolyear'
group by school_year
"					   
					   , 1);



	$res .= "<h2>Invitations</h2>";
	$res .= showRawQuery("Invitation Income by Type", 
"
select coa.description as Description,
        sum(coalesce(tic.total,0) + coalesce(inc.total,0)) as Total
from chart_of_accounts as coa
left join 
    (select account_number, sum(payment_amount) as total
     from leads_income_join as linj
     left join income 
              on linj.income_id = 
                income.income_id
        where income.school_year = '$schoolyear'
        group by income.account_number) 
    as inc
        on inc.account_number = coa.account_number
left join 
    (select account_number, sum(payment_amount) as total
     from tickets
     left join income 
              on tickets.income_id = 
                income.income_id
        where income.school_year = '$schoolyear'
        group by income.account_number) 
    as tic
        on tic.account_number = coa.account_number
group by coa.account_number
having Total > 0
order by Total desc

"					   
					   , 1);


	$res .= showRawQuery("Invitation  Counts", 
				 sprintf('select relation, 
			sum(if(invitations.family_id>0,0,1)) as Alumni_List ,
			sum(if(invitations.family_id>0,1,0)) as Family_Supplied ,
			count(distinct(lead_id)) as Total 
		from invitations 
		where 
			  invitations.school_year = "%s" 
   		group by relation 
		order by total desc',
		   $schoolyear));


	$res .= showRawQuery("Reservations from Invitations", 
				 "select  invitations.relation,
			sum(if(invitations.family_id>0,0,ticket_quantity)) as Alumni_List ,
			sum(if(invitations.family_id>0,ticket_quantity,0)) as Family_Supplied ,
			 sum(ticket_quantity)  as Total 
		from tickets
			left join invitations 
				on invitations.lead_id = tickets.lead_id
				and invitations.school_year  = \"$schoolyear\"
		where tickets.lead_id is not null 
			and tickets.ticket_quantity > 0
				and tickets.school_year = \"$schoolyear\"
		group by invitations.relation
		order by total desc" 
		);


	$res .= showRawQuery("Total Event Reservations (Paddles)", 
		   "select if(tickets.income_id > 0, 'Paid', 
				if(tickets.family_id > 0, 'Members', 'Freebies')) 
						as Payment_Type,
				count(springfest_attendee_id) as Total
			from springfest_attendees 
				left join tickets 
						on springfest_attendees.ticket_id = tickets.ticket_id
				where springfest_attendees.school_year = '$schoolyear'
				group by Payment_Type
				order by Payment_Type
"
					 		);
	return $res;
	 
}



////////////////////////MAIN


$cp = new coopPage( $debug);
print $cp->pageTop();
print $cp->topNavigation();

print $cp->stackPath();

$atd = new CoopView(&$cp, 'income', $none);


print "\n<hr /></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div id="centerCol">';



// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
	 
//// EDIT AND NEW //////
 case 'new':
 case 'edit':
	 break;


//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp);

	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END CARRIEREPORT -->

