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
       figlue
       companies
 */
showRawQuery("Total ALL income from ALL sources", 
  "select coa.description, 
	   sum(if(figlue.familyid>0 || companies.familyid>0,inc.amount,0)) 
			as member_paid ,
	   sum(if(figlue.familyid>0 || companies.familyid>0,0,inc.amount)) 
			as non_member_paid ,
	   sum(amount)  as total 
		from inc 
			   left join coa on inc.acctnum = coa.acctnum 
			   left join figlue on inc.incid = figlue.incid
			   left join raffle_income_join 
					   on inc.incid = raffle_income_join.incid
			   left join invitation_rsvps 
					   on inc.incid = invitation_rsvps.incid
			   left join companies_income_join
					   on inc.incid = companies_income_join.incid
					left join companies 
						on companies.company_id = 
							companies_income_join.company_id
		group by inc.acctnum order by total desc"
);


print "<h2>Solicitation</h2>";

showRawQuery("Income from Solicitation", 
	 "select coa.description, 
		   sum(if(companies.familyid>0,inc.amount,0)) 
				as member_paid ,
		   sum(if(companies.familyid>0,0,inc.amount)) 
				as non_member_paid ,
			sum(amount)  as total 
		from inc 
			left join companies_income_join 
				on companies_income_join.incid = inc.incid 
					left join companies 
						on companies.company_id = 
							companies_income_join.company_id
			left join coa on inc.acctnum = coa.acctnum 
		where companies_income_join.company_id is not null 
		group by inc.acctnum order by total desc"
	);

print "<h2>Invitations</h2>";

showRawQuery("Invitation Counts", 
	 "select relation, count(leadsid) as total 
		from leads 
		group by relation 
		order by total desc"
	);

showRawQuery("Income from Invitations", 
	 "select coa.description, sum(amount)  as total 
		from inc 
			left join invitation_rsvps 
				on invitation_rsvps.incid = inc.incid 
			left join coa on inc.acctnum = coa.acctnum 
		where invitation_rsvps.leadsid is not null 
		group by inc.acctnum order by total desc"
	);

showRawQuery("Tickets Sold from Invitations", 
	"select  leads.relation, sum(ticket_quantity)  as total 
		from invitation_rsvps
			left join leads on leads.leadsid = invitation_rsvps.leadsid
		where invitation_rsvps.leadsid is not null 
			and invitation_rsvps.ticket_quantity > 0
		group by leads.relation
		order by total desc"
	);


showRawQuery("Returned mail(bad address)", 
	"select relation, 
		sum(if(leads.familyid>1,0,1)) as alumni_list ,
		sum(if(leads.familyid>1,1,0)) as member_supplied ,
		count(leads.leadsid) as total
		from leads 
			left join families using (familyid) 
		where do_not_contact is not null 
				and do_not_contact > '0000-00-00' 
		group by relation 
		order by total desc"
	);

done();

?>

<!-- END CARRIEREPORT -->