<?php

	#  Copyright (C) 2004  ken restivo <ken@restivo.org>
	# 
	#  This program is free software; you can redistribute it and/or modify
	#  it under the terms of the GNU General Public License as published by
	#  the Free Software Foundation; either version 2 of the License, or
	#  (at your option) any later version.
	# 
	#  This program is distributed in the hope that it will be useful,
	#  but WITHOUT ANY WARRANTY; without even the implied warranty of
	#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	#  GNU General Public License for more details. 
	# 
	#  You should have received a copy of the GNU General Public License
	#  along with this program; if not, write to the Free Software
	#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

// <!-- CARRIEREPORT -->

require_once("first.inc");
require_once("shared.inc");


print "<html> <body>";


print "<h2>Totals</h2>";

/* familid's in 
       raffle_income_join
       families_income_join
       companies
 */
showRawQuery("Total ALL income from ALL sources", 
  "select chart_of_accounts.description, 
	   sum(if(families_income_join.family_id>0 || companies.family_id>0,income.payment_amount,0)) 
			as Member_Paid ,
	   sum(if(families_income_join.family_id>0 || companies.family_id>0,0,income.payment_amount)) 
			as Non_Member_Paid ,
	   sum(payment_amount)  as Total 
		from income 
			   left join chart_of_accounts on income.account_number = chart_of_accounts.account_number 
			   left join families_income_join on income.income_id = families_income_join.income_id
			   left join raffle_income_join 
					   on income.income_id = raffle_income_join.income_id
			   left join invitation_rsvps 
					   on income.income_id = invitation_rsvps.income_id
			   left join companies_income_join
					   on income.income_id = companies_income_join.income_id
					left join companies 
						on companies.company_id = 
							companies_income_join.company_id
		group by income.account_number order by total desc", 1
);

print "<h2>Reconciliation</h2>";

showRawQuery("Total ALL income by reconciliation status", 
  "select chart_of_accounts.description, 
	   sum(if(income.cleared_date>0,0,income.payment_amount)) 
			as not_cleared_yet ,
	   sum(if(income.cleared_date>0,income.payment_amount,0)) 
			as cleared ,
	   sum(payment_amount)  as total 
		from income 
			   left join chart_of_accounts on income.account_number = chart_of_accounts.account_number 
			   left join families_income_join on income.income_id = families_income_join.income_id
			   left join raffle_income_join 
					   on income.income_id = raffle_income_join.income_id
			   left join invitation_rsvps 
					   on income.income_id = invitation_rsvps.income_id
			   left join companies_income_join
					   on income.income_id = companies_income_join.income_id
					left join companies 
						on companies.company_id = 
							companies_income_join.company_id
		group by income.account_number order by total desc", 1
);


print "<h2>Solicitation</h2>";

showRawQuery("Income from Solicitation", 
	 "select chart_of_accounts.description, 
		   sum(if(companies.family_id>0,income.payment_amount,0)) 
				as Member_Paid ,
		   sum(if(companies.family_id>0,0,income.payment_amount)) 
				as Non_Member_Paid ,
			sum(payment_amount)  as Total 
		from income 
			left join companies_income_join 
				on companies_income_join.income_id = income.income_id 
					left join companies 
						on companies.company_id = 
							companies_income_join.company_id
			left join chart_of_accounts on income.account_number = chart_of_accounts.account_number 
		where companies_income_join.company_id is not null 
		group by income.account_number order by total desc" , 1
	);

print "<h2>Invitations</h2>";

showRawQuery("Invitation Counts", 
	 "select relation, 
			sum(if(leads.family_id>0,0,1)) as Alumni_List ,
			sum(if(leads.family_id>0,1,0)) as Family_Supplied ,
			count(lead_id) as Total 
		from leads 
		group by relation 
		order by total desc"
	);

showRawQuery("Income from Invitations", 
	 "select chart_of_accounts.description, 
			sum(if(leads.family_id>0,0,payment_amount)) as Alumni_List ,
			sum(if(leads.family_id>0,payment_amount,0)) as Family_Supplied ,
			sum(payment_amount)  as Total 
		from income 
			left join invitation_rsvps 
				on invitation_rsvps.income_id = income.income_id 
				left join leads on leads.lead_id = invitation_rsvps.lead_id
			left join chart_of_accounts on income.account_number = chart_of_accounts.account_number 
		where invitation_rsvps.lead_id is not null 
		group by income.account_number order by total desc" ,1
	);

showRawQuery("Income by Relationship", 
	"select  leads.relation,
			sum(if(leads.family_id>0,0,income.payment_amount)) as Alumni_List ,
			sum(if(leads.family_id>0,income.item_value,0)) as Family_Supplied ,
			 sum(income.item_value)  as Total 
		from invitation_rsvps
			left join income on invitation_rsvps.income_id = income.income_id
				left join leads on leads.lead_id = invitation_rsvps.lead_id
		where invitation_rsvps.lead_id is not null 
		group by leads.relation
		order by total desc" , 1
	);

showRawQuery("Tickets Sold from Invitations", 
	"select  leads.relation,
			sum(if(leads.family_id>0,0,ticket_quantity)) as Alumni_List ,
			sum(if(leads.family_id>0,ticket_quantity,0)) as Family_Supplied ,
			 sum(ticket_quantity)  as Total 
		from invitation_rsvps
			left join leads on leads.lead_id = invitation_rsvps.lead_id
		where invitation_rsvps.lead_id is not null 
			and invitation_rsvps.ticket_quantity > 0
		group by leads.relation
		order by total desc" 
	);


showRawQuery("Returned mail(bad address)", 
	"select relation, 
		sum(if(leads.family_id>0,0,1)) as Alumni_List ,
		sum(if(leads.family_id>0,1,0)) as Family_Supplied ,
		count(leads.lead_id) as Total
		from leads 
			left join families using (family_id) 
		where do_not_contact is not null 
				and do_not_contact > '0000-00-00' 
		group by relation 
		order by total desc"
	);

done();

?>

<!-- END CARRIEREPORT -->
