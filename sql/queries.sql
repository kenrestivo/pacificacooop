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
select last_name, first_name 
    from kids 
        left join attendance on kids.kid_id = attendance.kid_id 
        left join enrol on enrol.enrolid = attendance.enrolid 
    where enrol.sess = "PM";

-- contact info for all parents, showing who is and isn't a worker
select families.name, parents.last_name, parents.first_name , 
    parents.worker, families.phone, parents.email_address 
    from parents 
        left join families on parents.family_id = families.family_id 
    order by families.name, parents.last_name, parents.first_name;

-- expired licenses
select parents.last_name, parents.first_name, lic.expiration_date 
    from parents 
        left join lic on parents.parent_id = lic.parent_id 
    where parents.worker = "Yes" and (expiration_date is null
        or expiration_date < now())
    group by parents.last_name, parents.first_name;


-- give me ocntact info for all families who have expired insurance
-- XXX: this MAY be the worker, it may not, i don't know
-- XXX this is THROUHOGHLY FUX0RED! it ignores matches on the nonworker
select families.name, parents.last_name, parents.first_name, 
        ins.expiration_date, families.phone, parents.email_address
    from families 
        left join parents on parents.family_id = families.family_id 
        left join ins on parents.parent_id = ins.parent_id 
    where expiration_date is null or expiration_date < now()
    group by families.name;

-- all families and working parent info
select families.name, families.family_id, families.phone,
        parents.email_address
    from families 
        left join parents on parents.family_id = families.family_id
    where parents.worker = 'Yes'
    group by families.name;


-- all the working drivers' license expirations, WIHTOUT the family stuff
select max(lic.expiration_date) as exp, parents.last_name, parents.first_name, parents.parent_id, parents.family_id
    from lic 
        left join parents on lic.parent_id = parents.parent_id 
    where parents.worker= "Yes" 
    group by parents.parent_id
    order by exp desc;
    -- note: in an actualy scripted query, i'll add where parents.family_id =


-- all the insurance expirations, by parent_id, WIHTOUT the family stuff
select max(ins.expiration_date) as exp, parents.family_id, families.name,
        ins.policy_number, ins.companycompany_name
    from ins 
        left join parents on ins.parent_id = parents.parent_id 
        left join families on parents.family_id = families.family_id 
    group by parents.family_id
    order by exp desc;
    -- note: in an actualy scripted query, i'll add where parents.family_id =

--- THIS IS THE ROSTER!
 select kids.last_name, kids.first_name, enrollment.* 
    from kids 
        left join enrollment using (kid_id) 
	where enrollment.school_year = '2004-2005' 
	order by enrollment.am_pm_session, kids.last_name, kids.first_name;


-- show all springfest payments
select families.name, sum(income.payment_amount) as total
    from families
        left join families_income_join 
            on families.family_id = families_income_join.family_id
        left join income 
            on families_income_join.income_id = income.income_id
    where income.account_number = 1
    group by families.family_id
    order by families.family_id

--- show session families
select families.name, enrol.sess 
    from families
        left join kids on kids.family_id = families.family_id
        left join attendance on attendance.kid_id = kids.kid_id 
        left join enrol on enrol.enrolid = attendance.enrolid
    group by families.family_id
    order by enrol.sess, families.name

-- how bad do people do at leaving things to the last damn minute
 select date_format(entered, "%W, %b %d %Y") as dt, count(lead_id) as cnt 
        from leads 
    group by dt 
    order by entered;

-- HACKY temporary way to keep record of who was sent what!
insert into invitations (lead_id, school_year, family_id, relation, label_printed)
select distinct(leads.lead_id)  as lead_id,
    '2004-2005',
    leads.family_id,
    leads.relation,
	now()
    from leads
        left join families on families.family_id = leads.family_id
        left join kids on kids.family_id = leads.family_id
        left join enrollment on enrollment.kid_id = kids.kid_id 
     where  
            ((leads.family_id is null or leads.family_id < 1) 
             or relation = 'Alumni' or
             (enrollment.school_year = '2004-2005' 
                 and enrollment.dropout_date is NULL))
			and  (do_not_contact is null or do_not_contact > '2000-01-01')
       order by leads.last_name, leads.first_name

-- the invitations excel export report
select distinct(leads.lead_id)  as responsecode
        ,leads.salutation 
        ,leads.first_name  
        ,leads.last_name    
        ,leads.title
        ,leads.company 
        ,leads.address1   
        ,leads.address2
        ,leads.city   
        ,leads.state 
        ,leads.zip  
        ,leads.country
        ,families.name as familyname
    from leads
        left join invitations using (lead_id)
			left join families using (family_id)
     where  
            invitations.school_year = '2004-2005' 
       order by leads.last_name, leads.first_name


-- detailed list of payments
select  income.income_id, income.check_number, income.payer, 
        chart_of_accounts.item_description, income.payment_amount, families.name
    from income
        left join chart_of_accounts 
            on chart_of_accounts.account_number = income.account_number
        left join families_income_join 
            on families_income_join.income_id = income.income_id
        left join families 
            on families.family_id = families_income_join.family_id
    order by income.check_date desc

-- query for deleting/updating session stuff
select * from attendance 
        left join kids on attendance.kid_id = kids.kid_id 
        left join families on kids.family_id = families.family_id 
    where families.name like "%hearne%";

-- show the kids that need to be add/dropped
select attendance.*, kids.first_name, kids.last_name, families.name
    from attendance
        left join kids on kids.kid_id = attendance.kid_id
        left join families on kids.family_id = families.family_id
    order by families.name

-- for the delete checking
select kids.first_name, kids.last_name, enrol.school_year, enrol.sess
    from attendance
        left join enrol on attendance.enrolid = enrol.enrolid
        left join kids on kids.kid_id = attendance.kid_id
    where enrol.school_year = '2003-2004'  and attendance.dropout is null

-- find orphaned auctionss XXX BROKEN! i have new linkfields!
select auction_donation_items.*, families.name 
    from auction 
        left join auction_items_families_join
        on auction_items_families_join.auction_donation_item_id = auction_donation_items.auction_donation_item_id 
        left join families on families.family_id = auction_items_families_join.family_id 
    where auction_items_families_join.family_id is null 
    order by auction_donation_item_id

-- find orphaned checks (serious) XXX BROKEN! i have new linkfields!
select income.*, families.name 
    from income 
        left join families_income_join 
            on families_income_join.income_id = income.income_id 
        left join families 
            on families.family_id = families_income_join.family_id 
    where families_income_join.family_id is null 
    order by income_id

-- show the total auction item_value (for a family)
select families.name, sum(auction_donation_items.item_value) as item_value
    from families
        left join auction_items_families_join 
            on families.family_id = auction_items_families_join.family_id
        left join auction 
            on auction_items_families_join.auction_donation_item_id = 
                auction_donation_items.auction_donation_item_id
    group by families.family_id

-- money totals
select chart_of_accounts.item_description, sum(item_value)  as total 
    from income 
        left join chart_of_accounts 
            on income.account_number = chart_of_accounts.account_number 
    group by income.account_number order by total desc;

-- income specific to invites
select chart_of_accounts.item_description, sum(item_value)  as total 
    from income 
        left join invitation_rsvps 
            on invitation_rsvps.income_id = income.income_id 
        left join chart_of_accounts 
            on income.account_number = chart_of_accounts.account_number 
    where invitation_rsvps.lead_id is not null 
    group by income.account_number order by total desc;


--- audit trails
select date_format(updated, '%W, %M %D %Y %r') as updated, 
    index_id, name 
    from audit_trail 
        left join users on audit_trail.audit_user_id = users.user_id 
    where index_id < 1000 and table_name = 'leads' 
        and updated like "200411%" 
    order by updated ;


-- logins
select session_info.ip_addr, session_info.updated, users.name 
    from session_info
        left join users on users.user_id = session_info.user_id
    where session_info.user_id > 0
    order by updated desc;


--- privilege information
select user_privileges.* ,users.name 
    from user_privileges 
    left join users using (user_id) 
    where realm = 'packaging';

-- common. i want to know what realms i have available
select distinct(realm) from user_privileges
group by realm
order by realm;

-- tickets by family
select  families.name, sum(ticket_quantity)  as total 
    from invitation_rsvps
        left join leads on leads.lead_id = invitation_rsvps.lead_id
        left join families on families.family_id = leads.family_id
    where invitation_rsvps.lead_id is not null 
    group by families.name
    order by total desc;

-- tickets, summary
select  leads.relation, sum(ticket_quantity)  as total 
    from invitation_rsvps
        left join leads on leads.lead_id = invitation_rsvps.lead_id
    where invitation_rsvps.lead_id is not null 
    group by leads.relation
    order by total desc;

-- bad address
select relation, 
    sum(if(leads.family_id>1,1,0)) as family_supplied ,
    sum(if(leads.family_id>1,0,1)) as alumni_list ,
    count(leads.lead_id) as total
    from leads 
        left join families using (family_id) 
    where do_not_contact is not null 
            and do_not_contact > '0000-00-00' 
    group by relation 
    order by total desc;

-- show me the money! all of it, in this case
-- REDO with subqueries!
select chart_of_accounts.item_description, 
     sum(if(families_income_join.family_id>0 || 
            companies.family_id>0,income.payment_amount,0)) 
        as family_paid ,
        sum(amount)  as total 
    from income 
           left join chart_of_accounts on 
                income.account_number = chart_of_accounts.account_number 
           left join families_income_join 
                on income.income_id = families_income_join.income_id
           left join raffle_income_join 
                   on income.income_id = raffle_income_join.income_id
           left join invitation_rsvps 
                   on income.income_id = invitation_rsvps.income_id
           left join companies_income_join
                   on income.income_id = companies_income_join.income_id
                left join companies 
                    on companies.company_id = 
                        companies_income_join.company_id
    group by income.account_number order by total desc


--- nasty package-find join
select auction_donation_items.*,
    coalesce(concat(families.name, ' Family'), companies.company_name) as donor
from auction_packages_join 
left join auction_donation_items 
    on auction_donation_items.auction_donation_item_id = 
         auction_packages_join.auction_donation_item_id 
left join auction_items_families_join 
   on auction_items_families_join.auction_donation_item_id = 
        auction_donation_items.auction_donation_item_id 
    left join families 
           on families.family_id = auction_items_families_join.family_id 
left join companies_auction_join
    on companies_auction_join.auction_donation_item_id = 
        auction_donation_items.auction_donation_item_id
    left join companies 
        on companies_auction_join.company_id = companies.company_id
where package_id = 193;



-- the package summary
select package_type, package_number, package_title, 
        package_description,
        donated_by_text as generously_donated_by, package_value,
        starting_bid, bid_increment, item_type
    from packages
    where packages.school_year = '2004-2005'
    order by package_type, package_number, package_title, 
            package_description
        

-- nasty sponsorship join to check levels IN REAL CASH
select company_name,
        sum(inc.payment_amount) as cash_donations
from companies
left join 
    (select  sum(payment_amount) as payment_amount, company_id
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '2004-2005'
        group by cinj.company_id) 
    as inc
        on inc.company_id = companies.company_id
group by companies.company_id
having cash_donations >= 150
order by company_name;


-- show auction totals for SOLICIT AND for family auctions.
select families.name, sum(auction_donation_items.item_value) as item_value
    from auction
        left join auction_items_families_join 
            on auction_donation_items.auction_donation_item_id =
                auction_items_families_join.auction_donation_item_id
        left join companies_auction_join 
            on auction_donation_items.auction_donation_item_id = 
                companies_auction_join.auction_donation_item_id
        left join families 
            on coalesce(auction_items_families_join.family_id, 
                    companies_auction_join.family_id) = families.family_id
    group by coalesce(auction_items_families_join.family_id, 
                    companies_auction_join.family_id)
    order by families.name


-- yay! fix packages
    update packages 
        set package_number = concat("S0", right(package_number, 2)) 
    where length(package_number) < 4 and package_number like "S%";

-- tickets export
select ticket_quantity , item_value, last_name, first_name, address1 ,
        address2, city , state , zip , leads.lead_id as response_code 
    from invitation_rsvps 
        left join leads on invitation_rsvps.lead_id = leads.lead_id 
        left join income on invitation_rsvps.income_id = income.income_id 
    where ticket_quantity > 0  and school_year = '2004-2005'
    order by leads.last_name, leads.first_name;

-- the family summary
create temporary table enrolled_temp (
    name varchar(255),
    am_pm_session enum ('AM', 'PM'),
    family_id int(32) not null unique,
    phone varchar(20)
);
insert into enrolled_temp
select families.name, enrollment.am_pm_session,
    families.family_id , families.phone
        from families 
           left join kids on kids.family_id = families.family_id
           left join enrollment on kids.kid_id = enrollment.kid_id
        where enrollment.school_year = '2003-2004'
            and enrollment.dropout_date is null
    group by families.family_id
    order by enrollment.am_pm_session, families.name;

-- the enhancement hours, sold separately
select enrolled_temp.name as Family_Name,
    sum(if(enhancement_hours.hours, enhancement_hours.hours,0)) as Total, 
    enrolled_temp.am_pm_session as Session, enrolled_temp.phone as Phone
    from enrolled_temp
           left join parents 
               on parents.family_id = enrolled_temp.family_id
           left join enhancement_hours 
               on parents.parent_id = enhancement_hours.parent_id
    group by enrolled_temp.family_id
    having Total < 4
    order by enrolled_temp.am_pm_session, enrolled_temp.name;

-- so often used, it needs to be here
select user_privileges.realm, user_privileges.group_level, 
    user_privileges.user_level, users.name, user_privileges.privilege_id
    from users 
        left join user_privileges using (user_id) 
    where users.name like "%whatever%";

-- one-shot to convert the old-style to the new-style
insert into enrollment 
    (kid_id, school_year, am_pm_session, start_date, dropout_date) 
    select attendance.kid_id, enrol.school_year, enrol.sess, 
        attendance.start_date, attendance.dropout 
    from attendance left join enrol using (enrolid);

-- one-shot, family names in the leads which should be updated
    select leads.lead_id, leads.last_name, income.payer 
        from leads left join invitation_rsvps 
            on leads.lead_id = invitation_rsvps.lead_id 
        left join income on invitation_rsvps.income_id = income.income_id 
    where last_name like "%Family%" 
        and invitation_rsvps.income_id is not null;

-- all the auction items
select  auction_items_families_join.family_id  ,  
        companies_auction_join.company_id ,  
        companies_auction_join.family_id as family_id_company  , 
        auction_donation_items.quantity  ,  
        auction_donation_items.item_description  , 
        auction_donation_items.item_value  ,  
        auction_donation_items.item_type  ,  
        auction_donation_items.date_received  ,  
        auction_donation_items.location_in_garage  , 
        auction_donation_items.auction_donation_item_id  ,  
        auction_donation_items.school_year  ,  
        auction_donation_items.package_id 
    from auction_donation_items 
        left join auction_items_families_join 
            on auction_donation_items.auction_donation_item_id = 
                auction_items_families_join.auction_donation_item_id 
        left join families 
            on auction_items_families_join.family_id = families.family_id 
        left join companies_auction_join 
            on auction_donation_items.auction_donation_item_id = 
                companies_auction_join.auction_donation_item_id 
        left join companies 
            on companies_auction_join.company_id = companies.company_id 
    order by families.name asc, companies.company_name 



---- the infamous parent-popup query
select count(distinct(parents.parent_id)) as count, 
        parent_id, parents.last_name, parents.first_name 
    from parents 
        left join kids on parents.family_id = kids.family_id 
        left join enrollment on kids.kid_id = enrollment.kid_id 
    where enrollment.school_year = '2004-2005' 
    group by parents.last_name, parents.first_name 
    order by parents.last_name, parents.first_name

-- find the duplicate or non-existent workers!!
select name, families.family_id,
    sum(if(parents.worker = 'yes',1,0)) as worker_count 
    from families 
        left join parents on families.family_id = parents.parent_id 
    group by parents.family_id 
    order by worker_count desc, families.name;


--- ugly one-off import
CREATE TABLE temp (
  temp_id int(32) NOT NULL unique auto_increment,
  last_name varchar(255) default NULL,
  first_name varchar(255) default NULL,
  address varchar(255) default NULL,
    date_of_birth date default NULL, 
  PRIMARY KEY  (temp_id)
) ;

-- income by month
 select concat(monthname(check_date), ' ', year(check_date)) as Month, 
        sum(payment_amount) as Total 
    from income 
    where school_year = '2004-2005' 
    group by Month 
    order by check_date;


--- attempt to fix leads
select temp_name, invitation_rsvps.invitation_rsvps_id 
    from springfest_attendees 
        left join invitation_rsvps using (lead_id) 
    where invitation_rsvps.lead_id is not null;

--- find orphaned thankyous
-- would make a good subselect here: update where
 select * 
    from auction_donation_items 
        left join thank_you using(thank_you_id) 
    where auction_donation_items.thank_you_id is not null 
        and thank_you.thank_you_id is null;

-- find orphned thankyous
	select * from income 
		left join thank_you using (thank_you_id) 
		where income.thank_you_id is not null 
			and thank_you.thank_you_id is null;

-- reset all thankyous
-- USE ONLY FOR TESTING!
delete from thank_you;
update in_kind_donations set thank_you_id = NULL;
update income set thank_you_id = NULL;
update auction_donation_items set thank_you_id = NULL;

-- weirdo ticket fix
select tickets.lead_id, last_name, first_name 
from tickets 
	left join leads using (lead_id) 
order by last_name, first_name;

-- the. massive. company. query.
select concat_ws(' - ', company_name, concat_ws(' ', first_name, last_name)) 
    as Company,
        sum(inc.payment_amount) as cash_donations,
        sum(pur.payment_amount) as auction_purchases,
        sum(auct.item_value) as auction_donations,
        sum(iks.item_value) as in_kind_donations
from companies
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year = '2004-2005'
        group by caj.company_id) 
    as auct
        on auct.company_id = companies.company_id
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year = '2004-2005'
        group by cikj.company_id) 
    as iks
        on iks.company_id = companies.company_id
left join 
    (select  sum(payment_amount) as payment_amount, company_id
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '2004-2005'
        group by cinj.company_id) 
    as inc
        on inc.company_id = companies.company_id
left join 
    (select  payment_amount, company_id
     from springfest_attendees as atd
    left join auction_purchases  as ap
            on ap.springfest_attendee_id = 
                atd.springfest_attendee_id
     left join income 
              on ap.income_id = 
                income.income_id
        where income.school_year = '2004-2005'
        group by atd.company_id) 
    as pur
        on pur.company_id = companies.company_id
group by companies.company_id
having cash_donations > 0 
    or auction_purchases > 0 
    or auction_donations > 0 
    or in_kind_donations > 0
order by cash_donations desc, auction_purchases desc, 
    auction_donations desc, in_kind_donations desc;

--- solicitation by acctnum
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
        where school_year = '2004-2005'
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
        where income.school_year = '2004-2005'
        group by atd.company_id) 
    as pur
        on pur.account_number = coa.account_number
group by coa.account_number
having Before_Event >0 or At_Event > 0
order by Before_Event desc, At_Event desc;


--- orphans
select * from springfest_attendees 
where (lead_id < 1  or lead_id is null) 
	and (ticket_id < 1 or ticket_id is null) 
	and (company_id < 1 or company_id is null) 
	and (parent_id < 1 or parent_id is null);

-- who sold what
select families.name as Soliciting_family,
        sum(inc.payment_amount) as cash_donations,
        sum(auct.item_value) as auction_donations,
        sum(iks.item_value) as in_kind_donations
from families
left join 
    (select  caj.family_id, sum(item_value) as item_value
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year = '2004-2005'
        group by caj.family_id) 
    as auct
        on auct.family_id = families.family_id
left join 
    (select  cikj.family_id, sum(item_value) as item_value
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year = '2004-2005'
        group by cikj.family_id) 
    as iks
        on iks.family_id = families.family_id
left join 
    (select  cinj.family_id, sum(payment_amount) as payment_amount
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '2004-2005'
        group by cinj.family_id) 
    as inc
        on inc.family_id = families.family_id
group by families.family_id
having cash_donations > 0 or auction_donations > 0 or in_kind_donations > 0
order by cash_donations desc, 
    auction_donations desc, in_kind_donations desc;

--- invites by acctnum
select coa.description as Description,
        coalesce(sum(tic.total),0) + coalesce(sum(inc.total),0) as Total
from chart_of_accounts as coa
left join 
    (select account_number, sum(payment_amount) as total
     from leads_income_join as linj
     left join income 
              on linj.income_id = 
                income.income_id
        where income.school_year = '2003-2004'
        group by income.account_number) 
    as inc
        on inc.account_number = coa.account_number
left join 
    (select account_number, sum(payment_amount) as total
     from tickets
     left join income 
              on tickets.income_id = 
                income.income_id
        where income.school_year = '2003-2004'
        group by income.account_number) 
    as tic
        on tic.account_number = coa.account_number
group by coa.account_number
having Total > 0
order by Total desc;


-- income summary by family
select sum(payment_amount) as total
from income
    left join leads_income_join as licj
        on licj.income_id = income.income_id
where licj.lead_id in
        (select lead_id 
        from invitations
        where invitations.family_id = 56 and school_year = '2004-2005')	
	and income.school_year = '2004-2005';

-- ticket summary by family
select sum(payment_amount) as total
from income
    left join tickets
        on tickets.income_id = income.income_id
where tickets.lead_id in
        (select lead_id 
        from invitations
        where invitations.family_id = 56 and school_year = '2004-2005')	
	and income.school_year = '2004-2005';

---- unchoosen auction items TEST
select * from auction_donation_items
left join auction_packages_join using (auction_donation_item_id)
where (package_id != 202 
or auction_packages_join.auction_donation_item_id is null) and 
auction_donation_items.school_year = "2004-2005";

----- paid ads
select distinct income.income_id, income.payment_amount, ads.ad_id , 
    companies.company_name, companies.company_id
from income 
left join companies_income_join 
    on companies_income_join.income_id = income.income_id
left join ads on companies_income_join.company_id = ads.company_id  
left join companies on companies_income_join.company_id = companies.company_id 
where ads.ad_id is not null 
    and ads.school_year = '2004-2005' 
group by ads.ad_id
order by companies.company_name;

--- invites by leadid
select leads.lead_id,
        coalesce(sum(tic.total),0) + coalesce(sum(inc.total),0) 
                as payment_amount
from leads
left join 
    (select lead_id, sum(payment_amount) as total
     from leads_income_join as linj
     left join income 
              on linj.income_id = 
                income.income_id
        where income.school_year = '2004-2005'
        group by linj.lead_id) 
    as inc
        on leads.lead_id = inc.lead_id
left join 
    (select lead_id, sum(payment_amount) as total
     from tickets
     left join income 
              on tickets.income_id = 
                income.income_id
        where income.school_year = '2004-2005'
        group by tickets.lead_id) 
    as tic
        on tic.lead_id = leads.lead_id
group by leads.lead_id
having payment_amount > 0 and leads.lead_id = %d
order by payment_amount desc

---- find fucked up entries BROKEN
select leads.first_name, leads.last_name, income.payer 
from leads_income_join 
left join leads on leads.lead_id= leads_income_join.lead_id 
left join income on income.income.id = leads_income_join.income_id;

-- massive company query as needed by thankyou hackreport
select concat_ws(' - ', company_name, concat_ws(' ', first_name, last_name)) 
    as Company, 
        companies.company_id,
        coalesce(sum(inc.payment_amount),0) +
        coalesce(sum(pur.payment_amount),0) + 
        coalesce(sum(auct.item_value),0) + 
        coalesce(sum(iks.item_value),0) 
        as Total
from companies
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year = '2004-2005' 
        and adi.date_received > '2000-01-01'
        and adi.thank_you_id is null
        group by caj.company_id) 
    as auct
        on auct.company_id = companies.company_id
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year = '2004-2005'
        and ikd.date_received > '2000-01-01'
        and ikd.thank_you_id is null
        group by cikj.company_id) 
    as iks
        on iks.company_id = companies.company_id
left join 
    (select  sum(payment_amount) as payment_amount, company_id
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '2004-2005' 
        and income.thank_you_id is null
        group by cinj.company_id) 
    as inc
        on inc.company_id = companies.company_id
left join 
    (select  payment_amount, company_id
     from springfest_attendees as atd
    left join auction_purchases  as ap
            on ap.springfest_attendee_id = 
                atd.springfest_attendee_id
     left join income 
              on ap.income_id = 
                income.income_id
        where income.school_year = '2004-2005' 
        and income.thank_you_id is null
        group by atd.company_id) 
    as pur
        on pur.company_id = companies.company_id
group by companies.company_id
having Total > 0 
order by Company;


--- lead totals for thankyou hackreport
select concat_ws(' - ', concat_ws(' ', first_name, last_name), company ) 
    as Company, 
        leads.lead_id,
        coalesce(sum(tic.total),0) + coalesce(sum(inc.total),0) as Total
from leads
left join 
    (select lead_id, sum(payment_amount) as total
     from leads_income_join as linj
     left join income 
              on linj.income_id = 
                income.income_id
        where income.school_year = '2004-2005'
        and income.thank_you_id is null
        group by linj.lead_id) 
    as inc
        on leads.lead_id = inc.lead_id
left join 
    (select lead_id, sum(payment_amount) as total
     from tickets
     left join income 
              on tickets.income_id = 
                income.income_id
        where income.school_year = '2004-2005'
        and income.thank_you_id is null
        group by tickets.lead_id) 
    as tic 
        on tic.lead_id = leads.lead_id
group by leads.lead_id
having Total > 0
order by Company;

-- attemted massive thankyouhackreport
select concat_ws(' - ', company_name, concat_ws(' ', first_name, last_name)) 
    as Company, 
        companies.company_id as id, 'company_id' as id_name,
        coalesce(sum(inc.payment_amount),0) +
        coalesce(sum(pur.payment_amount),0) + 
        coalesce(sum(auct.item_value),0) + 
        coalesce(sum(iks.item_value),0) 
        as Total
from companies
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year = '2004-2005' 
        and adi.date_received > '2000-01-01'
        and adi.thank_you_id is null
        group by caj.company_id) 
    as auct
        on auct.company_id = companies.company_id
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year = '2004-2005'
        and ikd.date_received > '2000-01-01'
        and ikd.thank_you_id is null
        group by cikj.company_id) 
    as iks
        on iks.company_id = companies.company_id
left join 
    (select  sum(payment_amount) as payment_amount, company_id
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '2004-2005' 
        and income.thank_you_id is null
        group by cinj.company_id) 
    as inc
        on inc.company_id = companies.company_id
left join 
    (select  payment_amount, company_id
     from springfest_attendees as atd
    left join auction_purchases  as ap
            on ap.springfest_attendee_id = 
                atd.springfest_attendee_id
     left join income 
              on ap.income_id = 
                income.income_id
        where income.school_year = '2004-2005' 
        and income.thank_you_id is null
        group by atd.company_id) 
    as pur
        on pur.company_id = companies.company_id
group by companies.company_id
having Total > 0
UNION DISTINCT
select concat_ws(' - ', concat_ws(' ', first_name, last_name), company ) 
    as Company, 
        leads.lead_id as id, 'lead_id' as id_name,
        coalesce(sum(tic.total),0) + coalesce(sum(inc.total),0) as Total
from leads
left join 
    (select lead_id, sum(payment_amount) as total
     from leads_income_join as linj
     left join income 
              on linj.income_id = 
                income.income_id
        where income.school_year = '2004-2005'
        and income.thank_you_id is null
        group by linj.lead_id) 
    as inc
        on leads.lead_id = inc.lead_id
left join 
    (select lead_id, sum(payment_amount) as total
     from tickets
     left join income 
              on tickets.income_id = 
                income.income_id
        where income.school_year = '2004-2005'
        and income.thank_you_id is null
        group by tickets.lead_id) 
    as tic 
        on tic.lead_id = leads.lead_id
group by leads.lead_id
having Total > 0
order by Company;

--- EOF