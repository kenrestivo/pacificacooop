-- $Id$
-- various narsty join queries that are so hairy, i want to keep track of them

-- Copyright (C) 2003  ken restivo <ken@restivo.org>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details. 
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

-- just a test query. all kids.
select last, first 
	from kids 
		left join attendance on kids.kidsid = attendance.kidsid 
		left join enrol on enrol.enrolid = attendance.enrolid 
	where enrol.sess = "PM";

-- contact info for all parents, showing who is and isn't a worker
select families.name, parents.last, parents.first , parents.worker, families.phone, parents.email 
	from parents 
		left join families on parents.familyid = families.familyid 
	order by families.name, parents.last, parents.first;

-- expired licenses
select parents.last, parents.first, lic.expires 
	from parents 
		left join lic on parents.parentsid = lic.parentsid 
	where parents.worker = "Yes" and (expires is null or expires < now())
	group by parents.last, parents.first;


-- give me ocntact info for all families who have expired insurance
-- XXX: this MAY be the worker, it may not, i don't know
-- XXX this is THROUHOGHLY FUX0RED! it ignores matches on the nonworker
select families.name, parents.last, parents.first, ins.expires,
		families.phone, parents.email
	from families 
		left join parents on parents.familyid = families.familyid 
		left join ins on parents.parentsid = ins.parentsid 
	where expires is null or expires < now()
	group by families.name;

-- all families and working parent info
select families.name, families.familyid, families.phone,
		parents.email
	from families 
		left join parents on parents.familyid = families.familyid
	where parents.worker = 'Yes'
	group by families.name;


-- all the working drivers' license expirations, WIHTOUT the family stuff
select max(lic.expires) as exp, parents.last, parents.first, parents.parentsid, parents.familyid
	from lic 
		left join parents on lic.parentsid = parents.parentsid 
	where parents.worker= "Yes" 
	group by parents.parentsid
	order by exp desc;
	-- note: in an actualy scripted query, i'll add where parents.familyid =


-- all the insurance expirations, by parentsid, WIHTOUT the family stuff
select max(ins.expires) as exp, parents.familyid, families.name, ins.policynum, ins.companyname
	from ins 
		left join parents on ins.parentsid = parents.parentsid 
		left join families on parents.familyid = families.familyid 
	group by parents.familyid
	order by exp desc;
	-- note: in an actualy scripted query, i'll add where parents.familyid =

--- show kids and enrollment
select kids.*, enrol.sess 
	from kids 
		left join attendance on attendance.kidsid = kids.kidsid 
		left join enrol on enrol.enrolid = attendance.enrolid;

-- show all springfest payments
select families.name, sum(inc.amount) as total
	from families
		left join figlue on families.familyid = figlue.familyid
		left join inc on figlue.incid = inc.incid
	where inc.acctnum = 1
	group by families.familyid
	order by families.familyid

--- show session families
select families.name, enrol.sess 
	from families
		left join kids on kids.familyid = families.familyid
		left join attendance on attendance.kidsid = kids.kidsid 
		left join enrol on enrol.enrolid = attendance.enrolid
	group by families.familyid
	order by enrol.sess, families.name

-- how bad do people do at leaving things to the last damn minute
 select date_format(entered, "%W, %b %d %Y") as dt, count(leadsid) as cnt 
		from leads 
	group by dt 
	order by entered;

-- the excel export report
select leads.leadsid  as responsecode
		,leads.salut 
		,leads.first  
		,leads.last    
		,leads.title
		,leads.company 
		,leads.addr   
		,leads.addrcont
		,leads.city   
		,leads.state 
		,leads.zip  
		,leads.country
		,date_format(leads.entered, '%m/%d/%Y %T') as entered
		,families.name as familyname
	from leads
		left join families on leads.familyid = families.familyid
	order by leads.last, leads.first

-- detailed list of payments
select  inc.incid, inc.checknum, inc.payer, coa.description, inc.amount,  
		families.name
	from inc
		left join coa on coa.acctnum = inc.acctnum
		left join figlue on figlue.incid = inc.incid
		left join families on families.familyid = figlue.familyid
	order by inc.checkdate desc

-- query for deleting/updating session stuff
select * from attendance 
		left join kids on attendance.kidsid = kids.kidsid 
		left join families on kids.familyid = families.familyid 
	where families.name like "%hearne%";

-- show the kids that need to be add/dropped
select attendance.*, kids.first, kids.last, families.name
	from attendance
		left join kids on kids.kidsid = attendance.kidsid
		left join families on kids.familyid = families.familyid
	order by families.name

-- for the delete checking
select kids.first, kids.last, enrol.semester, enrol.sess
	from attendance
		left join enrol on attendance.enrolid = enrol.enrolid
		left join kids on kids.kidsid = attendance.kidsid
	where enrol.semester = '2003-2004'  and attendance.dropout is null

-- find orphaned auctionss XXX BROKEN! i have new linkfields!
select auction.*, families.name 
	from auction 
		left join faglue on faglue.auctionid = auction.auctionid 
		left join families on families.familyid = faglue.familyid 
	where faglue.familyid is null 
	order by auctionid

-- find orphaned checks (serious) XXX BROKEN! i have new linkfields!
select inc.*, families.name 
	from inc 
		left join figlue on figlue.incid = inc.incid 
		left join families on families.familyid = figlue.familyid 
	where figlue.familyid is null 
	order by incid

-- show the total auction amount (for a family)
select families.name, sum(auction.amount) as amount
	from families
		left join faglue on families.familyid = faglue.familyid
		left join auction on faglue.auctionid = auction.auctionid
	group by families.familyid

-- money totals
select coa.description, sum(amount)  as total 
	from inc 
		left join coa on inc.acctnum = coa.acctnum 
	group by inc.acctnum order by total desc;

-- specific to invites
select coa.description, sum(amount)  as total 
	from inc 
		left join invitation_rsvps 
			on invitation_rsvps.incid = inc.incid 
		left join coa on inc.acctnum = coa.acctnum 
	where invitation_rsvps.leadsid is not null 
	group by inc.acctnum order by total desc;


--- audit trails
select audit_trail.*, users.name 
    from audit_trail
        left join users on users.userid = audit_trail.audit_user_id
    order by updated desc;

-- logins
select session_info.ip_addr, session_info.updated, users.name 
    from session_info
        left join users on users.userid = session_info.user_id
    where session_info.user_id > 0
    order by updated desc;


--- privilege information
select privs.* ,users.name 
	from privs 
	left join users using (userid) 
	where realm = 'packaging';

-- tickets by family
select  families.name, sum(ticket_quantity)  as total 
    from invitation_rsvps
        left join leads on leads.leadsid = invitation_rsvps.leadsid
        left join families on families.familyid = leads.familyid
    where invitation_rsvps.leadsid is not null 
    group by families.name
    order by total desc;

-- tickets, summary
select  leads.relation, sum(ticket_quantity)  as total 
    from invitation_rsvps
        left join leads on leads.leadsid = invitation_rsvps.leadsid
    where invitation_rsvps.leadsid is not null 
    group by leads.relation
    order by total desc;

-- bad address
select relation, 
	sum(if(leads.familyid>1,1,0)) as family_supplied ,
	sum(if(leads.familyid>1,0,1)) as alumni_list ,
    count(leads.leadsid) as total
    from leads 
        left join families using (familyid) 
    where do_not_contact is not null 
            and do_not_contact > '0000-00-00' 
    group by relation 
    order by total desc;

-- massive beast!
select coa.description, 
   sum(if(figlue.familyid>0 || companies.familyid>0,inc.amount,0)) 
        as family_paid ,
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
    group by inc.acctnum order by total desc

--- EOF
