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


require_once('../includes/first.inc');
require_once('COOP/Page.php');
require_once('COOP/View.php');
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


    /// i need this for the totals
    $ev =& new CoopObject(&$cp, 'calendar_events', &$none);
    $ev->obj->query(sprintf('select date_format(event_date, "%%Y-%%m-%%d") as formatted_springfest from calendar_events where school_year = "%s" and event_id = %d',
                            $schoolyear, COOP_SPRINGFEST_EVENT_DATE
                            ));
    $ev->obj->fetch();
    $eventdate = $ev->obj->formatted_springfest;
    $ev->page->printDebug($eventdate,  1);


    $res .= $chooser; // hack

	
	$res .= "<h2>Totals for $schoolyear </h2>";

/* familid's in 
       raffle_income_join
       families_income_join
       companies
*/



	$res .= showRawQuery("Total ALL income from ALL sources", 
					   "select chart_of_accounts.description as Description, 
       sum(if(date_format(income.check_date, '%Y-%m-%d') not like '$eventdate' ,
                income.payment_amount,0)) 
            as Before_After_Event ,
       sum(if(date_format(income.check_date, '%Y-%m-%d') like '$eventdate' ,
                income.payment_amount,0)) 
            as Day_Of_Event ,
       sum(payment_amount)  as Total 
        from income 
                           left join chart_of_accounts 
                        on income.account_number = 
                                chart_of_accounts.account_number 
   where income.school_year = '$schoolyear'
        group by income.account_number order by total desc

", 1
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

	$res .= "<h2>Auction Donations (NOT REAL MONEY)</h2>";
	$res .= showRawQuery("NON-CASH ESTIMATED Auction Donations by Type", 
"
select 'Estimated \"Value\"', sum(if(auction_items_families_join.auction_donation_item_id is null, 0, 
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

	$res .= showRawQuery("NON-CASH ESTIMATED Package Values", 
"
select 'Non-Cash Estimated Package \"Value\"', 
sum(package_value) as Package_Estimate
from packages
where school_year = '$schoolyear'
group by school_year

"					   
					   , 1);



	$res .= "<h2>Invitations</h2>";

// XXX broken because it includes the MEMBER tickets.
// 	TODO: find a way to show only invitation income, not member stuff
// 	$res .= showRawQuery("Invitation Income by Type", 
// "
// select coa.description as Description,
//         sum(coalesce(tic.total,0) + coalesce(inc.total,0)) as Total
// from chart_of_accounts as coa
// left join 
//     (select account_number, sum(payment_amount) as total
//      from leads_income_join as linj
//      left join income 
//               on linj.income_id = 
//                 income.income_id
//         where income.school_year = '$schoolyear'
//         group by income.account_number) 
//     as inc
//         on inc.account_number = coa.account_number
// left join 
//     (select account_number, sum(payment_amount) as total
//      from tickets
//      left join income 
//               on tickets.income_id = 
//                 income.income_id
//         where income.school_year = '$schoolyear'
//         group by income.account_number) 
//     as tic
//         on tic.account_number = coa.account_number
// group by coa.account_number
// having Total > 0
// order by Total desc

// "					   
// 					   , 1);


    $res .= showRawQuery('Invitation Income by Contact Type/Source',
                         sprintf('select  invitations.relation,
    sum(if(invitations.family_id>0,0,payment_amount)) as Alumni_List ,
    sum(if(invitations.family_id>0,payment_amount,0)) as Family_Supplied ,
    sum(payment_amount)  as Total 
from invitations
   left join leads_income_join
       on invitations.lead_id = leads_income_join.lead_id
       and invitations.school_year  = "%s"
   left join tickets
       on invitations.lead_id = tickets.lead_id
       and invitations.school_year  = "%s"
   left join income on income.income_id = tickets.income_id or leads_income_join.income_id = income.income_id
where income.school_year = "%s" 
    and 
    (leads_income_join.lead_id is not null 
        or 
    (tickets.lead_id is not null 
        and tickets.ticket_quantity > 0
        and tickets.school_year = "%s"))
group by invitations.relation
order by total desc',
                                 $schoolyear, $schoolyear, 
                                 $schoolyear, $schoolyear),
                         1);





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
                  and (tickets.family_id is null or tickets.family_id < 1)
		group by invitations.relation
		order by total desc" 
		);


	$res .= showRawQuery(
        "Total Event Reservations (Paddles)", 
        "select ticket_type.description as Ticket_Type, 
            count(springfest_attendee_id) as Total
			from springfest_attendees 
				left join tickets 
						on springfest_attendees.ticket_id = tickets.ticket_id
                left join ticket_type 
                    on tickets.ticket_type_id = ticket_type.ticket_type_id
				where springfest_attendees.school_year = '$schoolyear'
				group by tickets.ticket_type_id
				order by ticket_type.description"
        );


	$res .= showRawQuery("Attendee Count (who actually attended)", 
"select ticket_type.description as Ticket_Type, 
    sum(if(invitations.relation = 'Friend', 1, 0)) as Friend,
    sum(if(invitations.relation = 'Relative', 1, 0)) as Relative,
    sum(if(invitations.relation = 'Alumni', 1, 0)) as Alumni,
    sum(if(invitations.relation = 'Coworker', 1, 0)) as Coworker,
    sum(if(invitations.relation = 'Other', 1, 0)) as Other,
    sum(if(tickets.company_id > 0, 1, 0)) as Solicitation,
                count(springfest_attendee_id) as Total
            from springfest_attendees 
                left join tickets 
                        on springfest_attendees.ticket_id = tickets.ticket_id
                left join ticket_type 
                    on tickets.ticket_type_id = ticket_type.ticket_type_id
                left join invitations on invitations.lead_id = tickets.lead_id
                and invitations.school_year = '$schoolyear'
                where springfest_attendees.school_year = '$schoolyear'
                     and attended = 'Yes'
                group by tickets.ticket_type_id
                order by ticket_type.description
"					 		);


	return $res;
	 
}



////////////////////////MAIN


$cp = new coopPage( $debug);
print $cp->pageTop();
print $cp->topNavigation();

print $cp->stackPath();

$atd = new CoopView(&$cp, 'income', $none);


print "\n<hr /></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div class="centerCol">';



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

