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

--- show enrolled families
select families.* 
from families
    left join kids on families.family_id = kids.family_id 
    left join enrollment on kids.kid_id = enrollment.kid_id
where enrollment.school_year = '2004-2005'
    and (enrollment.dropout_date < '1900-01-01'
    or enrollment.dropout_date is null)
group by families.family_id
order by families.name;

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
        and (invitations.label_printed is null 
            or invitations.label_printed < '2000-01-01')
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
select user_privileges.*, realms.realm, users.name 
from user_privileges left join realms using (realm_id) 
left join users on users.user_id = user_privileges.user_id  
where realm = 'jobs';

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



--- nasty leads sponsorsips
select leads.lead_id, 
(coalesce(sum(tic.total),0) + coalesce(sum(inc.total),0)) as payment_amount 
from leads 
left join 
    (select lead_id, sum(payment_amount) as total 
        from leads_income_join as linj 
            left join income on linj.income_id = income.income_id 
        where income.school_year = '2005-2006' 
        group by linj.lead_id) as inc 
   on leads.lead_id = inc.lead_id 
left join 
    (select lead_id, sum(payment_amount) as total 
        from tickets 
            left join income on tickets.income_id = income.income_id 
        where income.school_year = '2005-2006' 
        group by tickets.lead_id) as tic 
    on tic.lead_id = leads.lead_id 
group by leads.lead_id 
having payment_amount > 0 
order by payment_amount desc
;


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


-- show ticket families
select families.* 
from families
    left join kids on families.family_id = kids.family_id 
    left join enrollment on kids.kid_id = enrollment.kid_id
    left join families_income_join 
        on families_income_join.family_id = families.family_id
    left join income on 
        families_income_join.income_id = income.income_id
where enrollment.school_year = '2004-2005'
    and ((enrollment.dropout_date < '2000-01-01'
            or enrollment.dropout_date is null)
        or (account_number = 2 and payment_amount > 0 
            and income.school_year = '2004-2005'))
group by families.family_id
order by families.name;

--  nasty paddle report
select springfest_attendees.paddle_number, tickets.vip_flag, 
coalesce(leads.first_name, companies.first_name, parents.first_name) 
		as first_name,
coalesce(leads.last_name, companies.last_name, parents.last_name) as last_name,
coalesce(leads.company, companies.company_name) as company_name,
coalesce(leads.address1, companies.address1, families.address1) as address1,
coalesce(leads.address2, companies.address2) as address2,
coalesce(leads.city, companies.city) as city,
coalesce(leads.state, companies.state) as state,
coalesce(leads.zip, companies.zip) as zip,
coalesce(leads.phone, companies.phone, families.phone) as phone,
coalesce(leads.email_address, companies.email_address, families.email) as email_address,
income.payment_amount
from springfest_attendees
left join leads on springfest_attendees.lead_id = leads.lead_id
left join companies on springfest_attendees.company_id = companies.company_id
left join parents on springfest_attendees.parent_id = parents.parent_id
left join families on parents.family_id = families.family_id
left join tickets on springfest_attendees.ticket_id = tickets.ticket_id
		left join income on tickets.income_id = income.income_id
where springfest_attendees.school_year = '$sy'
order by last_name, first_name

--- nasty ticket summary
select tickets.ticket_id, 
concat_ws(' ', coalesce(leads.first_name, companies.first_name) ,
coalesce(leads.last_name, companies.last_name, concat(families.name, ' Family')),
coalesce(leads.company, companies.company_name),
coalesce(leads.address1, companies.address1, families.address1),
coalesce(leads.address2, companies.address2),
coalesce(leads.city, companies.city),
coalesce(leads.state, companies.state),
coalesce(leads.zip, companies.zip),
coalesce(leads.phone, companies.phone, families.phone),
coalesce(leads.email_address, companies.email_address, families.email)) as ticket_purchaser
from tickets
left join leads on tickets.lead_id = leads.lead_id
left join companies on tickets.company_id = companies.company_id
left join families on tickets.family_id = families.family_id
where tickets.school_year = '2004-2005'
order by ticket_purchaser;


-- super fucking nasty paddle report
select springfest_attendees.springfest_attendee_id, 
springfest_attendees.paddle_number, ticket_summary.vip_flag,
coalesce(leads.first_name, companies.first_name, parents.first_name) 
        as first_name,
coalesce(leads.last_name, companies.last_name, parents.last_name) as last_name,
coalesce(leads.company, companies.company_name) as company_name,
coalesce(leads.address1, companies.address1, families.address1) as address1,
coalesce(leads.address2, companies.address2) as address2,
coalesce(leads.city, companies.city) as city,
coalesce(leads.state, companies.state) as state,
coalesce(leads.zip, companies.zip) as zip,
coalesce(leads.phone, companies.phone, families.phone) as phone,
coalesce(leads.email_address, companies.email_address, families.email) as email_address,
ticket_summary.ticket_purchaser,
truncate(income.payment_amount / ticket_summary.ticket_quantity,2) as payment_amount,
coalesce(springfest_attendees.ticket_id, springfest_attendees.lead_id, springfest_attendees.parent_id, springfest_attendees.company_id) as empty_hack
from springfest_attendees
left join leads on springfest_attendees.lead_id = leads.lead_id
left join companies on springfest_attendees.company_id = companies.company_id
left join parents on springfest_attendees.parent_id = parents.parent_id
left join families on parents.family_id = families.family_id
left join
(select tickets.ticket_id, tickets.vip_flag, tickets.income_id, 
    tickets.school_year, tickets.ticket_quantity,
    concat_ws(' ', coalesce(leads.first_name, companies.first_name) ,
    coalesce(leads.last_name, companies.last_name, 
        concat(families.name, ' Family')),
    coalesce(leads.company, companies.company_name),
    coalesce(leads.address1, companies.address1, families.address1),
    coalesce(leads.address2, companies.address2),
    coalesce(leads.city, companies.city),
    coalesce(leads.state, companies.state),
    coalesce(leads.zip, companies.zip),
    coalesce(leads.phone, companies.phone, families.phone),
    coalesce(leads.email_address, companies.email_address, families.email)) 
        as ticket_purchaser,
    coalesce(leads.first_name, companies.first_name) as first,
    coalesce(leads.last_name, companies.last_name, 
        concat(families.name, ' Family')) as last
    from tickets
    left join leads on tickets.lead_id = leads.lead_id
    left join companies on tickets.company_id = companies.company_id
    left join families on tickets.family_id = families.family_id
) as ticket_summary 
    on ticket_summary.ticket_id = springfest_attendees.ticket_id
left join income on ticket_summary.income_id = income.income_id
where springfest_attendees.school_year = '2005-2006'
order by 
empty_hack desc,
coalesce(leads.last_name, companies.last_name, parents.last_name, ticket_summary.last),
coalesce(leads.first_name, companies.first_name, parents.first_name, ticket_summary.first);

--- copy the companies to leads, so they're there
-- this will be critical once i yank companies.
insert into leads (first_name, last_name, salutation, title, company, 
    address1, address2, city, state, zip, country, phone, company_id, 
	do_not_contact, source_id) 
select first_name, last_name, salutation, title, company_name, address1, 
    address2, city, state, zip, country, phone, company_id , do_not_contact, 
from companies;


---shit
select springfest_attendee_id, coalesce(springfest_attendees.ticket_id, springfest_attendees.lead_id, springfest_attendees.parent_id, springfest_attendees.company_id) as empty_hack 
from springfest_attendees 
where school_year = '2004-2005' 
order by empty_hack desc, springfest_attendee_id asc;

-- who bought the foul things
select
packages.package_number,
packages.package_title,
springfest_attendees.paddle_number,
coalesce(leads.first_name, companies.first_name, parents.first_name) 
        as first_name,
coalesce(leads.last_name, companies.last_name, parents.last_name) as last_name,
coalesce(leads.company, companies.company_name) as company_name,
coalesce(leads.address1, companies.address1, families.address1) as address1,
coalesce(leads.address2, companies.address2) as address2,
coalesce(leads.city, companies.city) as city,
coalesce(leads.state, companies.state) as state,
coalesce(leads.zip, companies.zip) as zip,
coalesce(leads.phone, companies.phone, families.phone) as phone,
coalesce(leads.email_address, companies.email_address, families.email) as email_address,
packages.starting_bid,
auction_purchases.package_sale_price
from packages
left join  auction_purchases on auction_purchases.package_id = 
      packages.package_id
left join springfest_attendees on springfest_attendees.springfest_attendee_id =
      auction_purchases.springfest_attendee_id
left join leads on springfest_attendees.lead_id = leads.lead_id
left join companies on springfest_attendees.company_id = companies.company_id
left join parents on springfest_attendees.parent_id = parents.parent_id
left join families on parents.family_id = families.family_id
where auction_purchases.package_sale_price > 1 
and packages.school_year = '2004-2005'
order by packages.package_number


-- IMPORTANT
-- unified perms checking code. holy shit. 
select 
table_permissions.table_name, table_permissions.field_name,
max(if((upriv.max_user <= table_permissions.user_level or
table_permissions.user_level is null or table_permissions.user_level < 0), 
upriv.max_user, table_permissions.user_level)) as cooked_user,
max(if((upriv.max_group >=  table_permissions.group_level or
table_permissions.group_level is null or table_permissions.group_level < 0), 
upriv.max_group, NULL )) as cooked_group,
 max(if((upriv.max_user > table_permissions.menu_level or
table_permissions.menu_level is null or table_permissions.menu_level < 0), 
upriv.max_user, NULL)) as cooked_menu,
max(if((upriv.max_year > table_permissions.user_level or table_permissions.year_level is null or table_permissions.year_level < 0),
upriv.max_year, table_permissions.year_level)) as cooked_year
from table_permissions 
left join 
(select max(user_level) as max_user, max(group_level) as max_group, 
max(year_level) as max_year,
91 as user_id, realm_id
from user_privileges 
where user_id = 91 
or ((user_id < 1 or user_id is null) and group_id in 
(select group_id from users_groups_join 
where user_id = 91)) 
group by realm_id 
order by realm_id) as upriv
on upriv.realm_id = table_permissions.realm_id 
where user_id = 91 and table_name = 'leads'
group by user_id,table_name,field_name



-- UN MAXED for debuggin g NULL issues
select 
table_permissions.table_name, table_permissions.field_name,
if((upriv.max_user <= table_permissions.user_level or
table_permissions.user_level is null), 
upriv.max_user, table_permissions.user_level) as cooked_user,
if((upriv.max_group >=  table_permissions.group_level or
table_permissions.group_level is null), 
upriv.max_group, NULL ) as cooked_group,
if((upriv.max_user > table_permissions.menu_level or
table_permissions.menu_level is null), 
upriv.max_user, NULL) as cooked_menu,
if(upriv.max_year > table_permissions.user_level,
upriv.max_year, table_permissions.year_level) as cooked_year
from table_permissions 
left join 
(select max(user_level) as max_user, max(group_level) as max_group, 
max(year_level) as max_year,
91 as user_id, realm_id
from user_privileges 
where user_id = 91 
or ((user_id < 1 or user_id is null) and group_id in 
(select group_id from users_groups_join 
where user_id = 91)) 
group by realm_id 
order by realm_id) as upriv
on upriv.realm_id = table_permissions.realm_id 
where user_id = 91 and table_name = 'leads'



-- TODO: this'd make a nice screen
--- shorter for debugging what teh FUCK is going on?
select max(user_level) as max_user, max(group_level) as max_group, 
max(year_level) as max_year, 
91 as user_id, short_description
from user_privileges 
left join realms on user_privileges.realm_id = realms.realm_id
where user_id = 91 
or ((user_id < 1 or user_id is null) and group_id in 
(select group_id from users_groups_join 
where user_id = 91)) 
group by user_privileges.realm_id 
order by user_privileges.realm_id



-- another thign useful for WTF
select 
table_permissions.table_name, table_permissions.field_name,
table_permissions.realm_id,
table_permissions.user_level,
table_permissions.group_level,
table_permissions.user_level,
 table_permissions.year_level from table_permissions where
table_name in('kids', 'families')
group by table_name,field_name
\G

--- JUST the users
select max(user_level) as max_user, max(group_level) as max_group, 
91 as user_id, realm_id
from user_privileges 
where user_id = 91 or 
((user_id < 1 or user_id is null) and group_id in 
(select
group_id from users_groups_join where user_id = 91)) 
group by realm_id 
order by realm_id;


---shorter version for auld auth
-- XXX is this broken? go back and have a look. maybe not.
select max(user_level) as user_level, max(group_level) as group_level,
91 as user_id, realm
from user_privileges
left join realms on user_privileges.realm_id = realms.realm_id 
where realm = 'invitations' 
and (user_id = 91 
or ((user_id < 1 or user_id is null) and group_id in 
(select group_id from users_groups_join 
where user_id = 91)))
group by realm 


-- ONE TIME ONLY query to populate workers table,
-- from contents of old parents table
insert into workers (parent_id, am_pm_session, school_year) 
(select distinct parent_id, enrollment.am_pm_session, school_year 
from parents 
left join kids on parents.family_id = kids.family_id 
left join enrollment on enrollment.kid_id = kids.kid_id 
where parents.worker = 'yes' 
order by parents.last_name, parents.first_name, school_year);


---blog, updated to show max
select blog_entry.*,
date_format(audits.updated, "%a %m/%d/%Y %l:%i%p") as update_human,
users.name
from (select * from audit_trail as aud1,
    (select table_name, index_id, max(updated) as updated
        from audit_trail
        group by table_name, index_id) as audmax
    where aud1.table_name = "blog_entry"
          and audmax.table_name = aud1.table_name 
          and audmax.index_id = aud1.index_id
          and aud1.updated = audmax.updated) as audits
left join blog_entry
on audits.index_id = blog_entry.blog_entry_id  
and audits.table_name = "blog_entry"
left join users on audits.audit_user_id = users.user_id
where show_on_members_page = "yes"
 group by blog_entry_id
order by updated desc
limit 4
\G

--- so common, i need to make a web page for it. oh wait, i already do!
select field_name, table_name as tbl,
user_level as usrlvl, group_level as grplvl, realm_id as rlm 
from table_permissions 
order by table_name,field_name;

-- needign familyide
select sum(if(field_name = 'family_id', 1, 0)) as present, 
realm_id, table_name 
from table_permissions  
group by table_name, realm_id
having present <1 
order by table_name ;



-- the DISASTER of report perms
select 
report_permissions.report_name, report_permissions.page,
max(if((upriv.max_group > report_permissions.menu_level or
report_permissions.menu_level is null), 
upriv.max_group, NULL)) as cooked_menu
from report_permissions 
left join 
(select max(user_level) as max_user, max(group_level) as max_group, 
91 as user_id, realm_id
from user_privileges 
where user_id = 91 
or ((user_id < 1 or user_id is null) and group_id in 
(select group_id from users_groups_join 
where user_id = 91)) 
group by realm_id 
order by realm_id) as upriv
on upriv.realm_id = report_permissions.realm_id 
where user_id = 91 
group by user_id,report_name,report_permissions.realm_id


-------AGAIN, with feeling
select 
report_permissions.report_name, report_permissions.page,
max(if((upriv.max_group > report_permissions.menu_level or
report_permissions.menu_level is null), 
upriv.max_group, NULL)) as cooked_menu
from report_permissions 
left join 
(select max(user_level) as max_user, max(group_level) as max_group, 
91 as user_id, realm_id
from user_privileges 
where user_id = 91 
or ((user_id < 1 or user_id is null) and group_id in 
(select group_id from users_groups_join 
where user_id = 91)) 
group by realm_id 
order by realm_id) as upriv
on upriv.realm_id = report_permissions.realm_id 
where user_id = 91 and report_permissions.realm_id = 3
group by user_id,report_name,report_permissions.realm_id

---- rasta importation errors. fuck it.
select count(parent_id) as size, name 
from parents 
left join families using (family_id) 
group by parents.family_id 
having size > 2;


--- much harder to find the errors.
select count(kid_id) as size, name 
from kids
left join families using (family_id) 
group by kids.family_id 
having size > 1;


---  perms showing
select description 
from access_levels 
where access_level_id <= 800 and access_level_id > 0 
order by access_level_id;

--- the rasta export
--"Last Name","Mom Name *","Dad/Partner *","Child ",DOB,Address,Phone,Email,M,Tu,W,Th,F,"School Job"
select  enrollment_id, kids.last_name as kid_last, 
concat(moms.first_name, " ", 
moms.last_name) as mom,
concat(dads.first_name, " ", dads.last_name) as dad, 
kids.first_name as kid_first, 
date_format(kids.date_of_birth, "%m/%d/%Y") as human_date, 
families.address1, 
families.phone, 
families.email,
enrollment.monday, enrollment.tuesday, enrollment.wednesday, 
enrollment.thursday, enrollment.friday, job_descriptions.summary as school_job,
enrollment.am_pm_session, enrollment.start_date, enrollment.dropout_date,
workers.workday, workers.epod, workers.am_pm_session, "2005-2006" as school_year,
(now() >= brings_baby.baby_due_date and now()< brings_baby.baby_too_old_date) as brings_baby
from enrollment
left join kids on enrollment.kid_id = kids.kid_id
left join parents as dads 
on dads.family_id = kids.family_id and dads.type <> "Mom"
left join parents as moms
on moms.family_id = kids.family_id and moms.type = "Mom"
left join families on kids.family_id = families.family_id
left join job_assignments 
on kids.family_id = job_assignments.family_id 
and job_assignments.school_year = "2005-2006"
left join job_descriptions 
on job_descriptions.job_description_id = job_assignments.job_description_id
left join workers on (workers.parent_id = moms.parent_id or workers.parent_id =dads.parent_id) and workers.school_year = "2005-2006"
left join brings_baby on workers.worker_id = brings_baby.worker_id
where enrollment.school_year = "2005-2006" and enrollment.am_pm_session = "AM"
group by enrollment_id
order by enrollment.am_pm_session, kids.last_name, kids.first_name
            


--- meetings to date
select calendar_events.* from calendar_events
where event_id = 2 
and calendar_events.school_year = '2005-2006'
and calendar_events.event_date < now()



--- everything for family attendance! yow.
select calendar_events.event_date, calendar_events.event_id,
family_attendance.hours, family_attendance.family_id
from calendar_events
left join (
select  calendar_event_id, enrolled_parents.family_id, sum(hours) as hours
from parent_ed_attendance
left join 
(
select distinct parents.parent_id, parents.family_id
from parents 
left join kids on kids.family_id = parents.family_id
left join enrollment on enrollment.kid_id = kids.kid_id 
where enrollment.school_year = '2005-2006'
order by parent_id
) as enrolled_parents
on enrolled_parents.parent_id = parent_ed_attendance.parent_id
group by enrolled_parents.family_id
order by enrolled_parents.family_id
) as family_attendance
on calendar_events.calendar_event_id = family_attendance.calendar_event_id
where event_id = 2 
and calendar_events.school_year = '2005-2006'
and calendar_events.event_date < now()



--- select all parent ed attendance
select  calendar_event_id, enrolled_parents.family_id, sum(hours) as hours
from parent_ed_attendance
left join 
(
select distinct parents.parent_id, parents.family_id
from parents 
left join kids on kids.family_id = parents.family_id
left join enrollment on enrollment.kid_id = kids.kid_id 
where enrollment.school_year = '2005-2006'
order by parent_id
) as enrolled_parents
on enrolled_parents.parent_id = parent_ed_attendance.parent_id
group by enrolled_parents.family_id
order by enrolled_parents.family_id


-- currently enrolled parents. goddammit. i need this everywhere.
select distinct parents.parent_id, parents.family_id
from parents 
left join kids on kids.family_id = parents.family_id
left join enrollment on enrollment.kid_id = kids.kid_id 
where enrollment.school_year = '2005-2006'
order by parent_id


--- fix null users
insert into users (name, family_id) 
(select concat(families.name, ' Family'), families.family_id 
from families 
left join users using (family_id) 
where user_id is null);

--- summary of workdays
select  
workday, sum(if(am_pm_session = 'AM', 1,0 )) as AM, 
sum(if(am_pm_session = 'PM', 1,0 )) as PM
from workers 
where school_year = '2005-2006'
group by  workday
order by  workday

select  
epod, sum(if(am_pm_session = 'AM', 1,0 )) as AM, 
sum(if(am_pm_session = 'PM', 1,0 )) as PM
from workers 
where school_year = '2005-2006'
group by  epod
order by  epod


--- the enrollment summary
select  am_pm_session, sum(monday) as monday, sum(tuesday) as tuesday, 
sum(wednesday) as wednesday, 
sum(thursday) as thursday, sum(friday) as friday
from enrollment
where enrollment.school_year = "2005-2006" 
group by am_pm_session
order by enrollment.am_pm_session


--- territories
select description, count(company_id) as number_of_companies 
from companies 
left join territories using (territory_id)  
group by companies.territory_id 
order by description;


--- territory summary
select territories.description as Territory,
        sum(inc.payment_amount) as cash_donations,
        sum(pur.payment_amount) as auction_purchases,
        sum(auct.item_value) as auction_donations,
        sum(iks.item_value) as in_kind_donations
from territories
left join companies 
    on companies.territory_id = territories.territory_id
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year = '$schoolyear'
        group by caj.company_id) 
    as auct
        on auct.company_id = companies.company_id
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year = '$schoolyear'
        group by cikj.company_id) 
    as iks
        on iks.company_id = companies.company_id
left join 
    (select  sum(payment_amount) as payment_amount, company_id
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '$schoolyear'
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
        where income.school_year = '$schoolyear'
        group by atd.company_id) 
    as pur
        on pur.company_id = companies.company_id
group by territories.territory_id
having cash_donations > 0 or auction_purchases > 0 or auction_donations > 0 or in_kind_donations > 0
order by cash_donations desc, auction_purchases desc, 
    auction_donations desc, in_kind_donations desc 

-- not very useful ad guess for 2003. i'd have to write more intelligent code
-- or just manually enter them from the previous year
select company_id, payment_amount, ad_size_description 
from companies_income_join left join income using (income_id) 
left join ad_sizes on ad_price = payment_amount 
where account_number = 5 and income.school_year = '2003-2004';



-- the currently enrolled users, dammit. do NOT mess with this!
select distinct families.family_id, families.email
from families
    left join kids on families.family_id = kids.family_id 
    left join enrollment on kids.kid_id = enrollment.kid_id 
where enrollment.school_year = '2005-2006'
and (enrollment.dropout_date < '1900-01-01'
    or enrollment.dropout_date is null)
group by families.family_id
order by families.name;




-- everyone who has this realm by way of themselves or a group
select distinct users.user_id, users.family_id, enrolled.email
from users
left join user_privileges on users.user_id = user_privileges.user_id
left join
(select distinct families.family_id, families.email
from families
    left join kids on families.family_id = kids.family_id 
    left join enrollment on kids.kid_id = enrollment.kid_id 
where enrollment.school_year = '2005-2006'
and (enrollment.dropout_date < '1900-01-01'
    or enrollment.dropout_date is null)
group by families.family_id
order by families.name
) as enrolled on enrolled.family_id = users.family_id
where 
 enrolled.email > ' '
and (user_privileges.realm_id = 23 
or users.user_id in 
(select distinct users_groups_join.user_id
from users_groups_join 
left join user_privileges 
on users_groups_join.group_id = user_privileges.group_id
where user_privileges.realm_id = 23 ))



-- privs for all realms, all users, all groups
select users.user_id, 
max(user_level) as max_user, max(group_level) as max_group,
user_privileges.realm_id
from users
left join user_privileges
   on users.user_id = user_privileges.user_id
       or user_privileges.group_id in 
           (select group_id 
           from users_groups_join 
            where user_id = users.user_id)
group by users.user_id, realm_id
order by users.user_id, realm_id;




-- privs for all realms, all groups, ONLY enrolled users
select enrolled.user_id, 
max(user_level) as max_user, max(group_level) as max_group, 
max(year_level) as max_year,
user_privileges.realm_id
from 
(select distinct users.user_id, families.family_id, families.email
   from users
        left join families on families.family_id = users.family_id
       left join kids on families.family_id = kids.family_id 
       left join enrollment on kids.kid_id = enrollment.kid_id 
   where enrollment.school_year = '2005-2006'
   and (enrollment.dropout_date < '1900-01-01'
       or enrollment.dropout_date is null)
   group by families.family_id
   order by families.name) as enrolled
left join user_privileges
   on enrolled.user_id = user_privileges.user_id
       or user_privileges.group_id in 
           (select group_id 
           from users_groups_join 
            where user_id = enrolled.user_id)
group by enrolled.user_id, realm_id
order by enrolled.user_id, realm_id;


-- the massive perms query, for all enrolled users with subscriptions
select subscriptions.*, upriv.user_id, upriv.family_id,
table_permissions.table_name, table_permissions.field_name,
max(if((upriv.max_user <= table_permissions.user_level or
table_permissions.user_level is null), 
upriv.max_user, table_permissions.user_level)) as cooked_user,
max(if((upriv.max_group >=  table_permissions.group_level or
table_permissions.group_level is null), 
upriv.max_group, NULL )) as cooked_group,
 max(if((upriv.max_user > table_permissions.menu_level or
table_permissions.menu_level is null), 
upriv.max_user, NULL)) as cooked_menu,
max(if((upriv.max_year > table_permissions.user_level or table_permissions.year_level is null),
upriv.max_year, table_permissions.year_level)) as cooked_year
from subscriptions
left join table_permissions 
    on table_permissions.realm_id = subscriptions.realm_id
left join 
(select enrolled.user_id, enrolled.family_id,
    max(user_level) as max_user, max(group_level) as max_group, 
    max(year_level) as max_year,
    user_privileges.realm_id
    from 
    (select distinct users.user_id, families.family_id, families.email
        from users
             left join families on families.family_id = users.family_id
            left join kids on families.family_id = kids.family_id 
            left join enrollment on kids.kid_id = enrollment.kid_id 
        where enrollment.school_year = "2005-2006"
        and (enrollment.dropout_date < "1900-01-01"
            or enrollment.dropout_date is null)
        group by families.family_id
        order by families.name) as enrolled
    left join user_privileges
        on enrolled.user_id = user_privileges.user_id
             or user_privileges.group_id in 
                 (select group_id 
                   from users_groups_join 
                   where user_id = enrolled.user_id)
     group by enrolled.user_id, realm_id
     order by enrolled.user_id, realm_id) as upriv
on upriv.realm_id = table_permissions.realm_id 
    and upriv.user_id = subscriptions.user_id
where  table_name = "blog_entry" 
    and field_name is null 
    and subscription_id is not null
group by subscriptions.user_id,table_name



---- grab all of last years's invitations, and invite them this year!
--- NOTE! will need to make this a script people can do!
insert into invitations
(school_year, lead_id, family_id, relation)
-- all the most recent years' invitations
(select '2005-2006', lead_id, invitations.family_id, relation
from invitations
    left join
    ---- last enrolled year for currently-enrolled families
    (select families.family_id, families.name,
        max(enrollment.school_year) as last_enrolled_year
        from  families
          left join kids on families.family_id = kids.family_id 
              left join enrollment on kids.kid_id = enrollment.kid_id
                    and enrollment.school_year <> '2005-2006'
            left join (select distinct families.family_id
                    from families
                        left join kids on families.family_id = kids.family_id 
                        left join enrollment on kids.kid_id = enrollment.kid_id 
                    where enrollment.school_year = "2005-2006"
                    and (enrollment.dropout_date < "1900-01-01"
                        or enrollment.dropout_date is null)
                    group by families.family_id
                    order by families.name) as enrolled
                on enrolled.family_id = families.family_id     
        where enrolled.family_id is not null  and enrolled.family_id > 0
            and enrollment.school_year is not null
         group by family_id) as previous
    on previous.family_id = invitations.family_id 
        and previous.last_enrolled_year = invitations.school_year
where previous.family_id > 0
order by previous.family_id)
    


---- carry forward last year's alumni.
---- ALSO will neeed this, until i merge all the alumni into leads/parents
insert into invitations 
(school_year, lead_id, family_id, relation) 
(select '2005-2006', lead_id, family_id, relation 
from invitations 
where (family_id = 0 or relation == 'Alumni') and school_year = '2004-2005');


--- last audit. used often
select * from audit_trail 
where audit_trail_id = (select max(audit_trail_id) from audit_trail) \G

-- one-off to fix invites stuff
select distinct leads.lead_id 
from audit_trail 
    left join leads on leads.lead_id = index_id 
    left join invitations on index_id = invitations.lead_id 
where audit_user_id = 118 and table_name = 'leads' and invitation_id is null;



--- WHY is this a syntax error???!
SELECT *, families.family_id as SAFE_family_id , 
max (school_year) as school_year , families.family_id as family_id 
FROM families 
LEFT JOIN kids ON kids.family_id=families.family_id 
LEFT JOIN enrollment ON enrollment.kid_id=kids.kid_id 
WHERE (enrollment.school_year like "%" or enrollment.school_year is null 
or enrollment.school_year < "1900-01-01") 
GROUP BY families.family_id 
ORDER BY school_year desc , families.name 


-- ugly worker's query
--- NOTE!!! this is the enrolled subquery. use it *everywhere*... stored proc?
select  distinct
workday, sum(if(workers.am_pm_session = "AM" and enrolled.family_id is not null, 1,0 )) as AM, 
sum(if(workers.am_pm_session = "PM" and enrolled.family_id is not null, 1,0 )) as PM
from workers
left join parents on parents.parent_id = workers.parent_id
left join 
       (select distinct families.family_id
                    from families
                        left join kids on families.family_id = kids.family_id 
                        left join enrollment on kids.kid_id = enrollment.kid_id 
                    where enrollment.school_year = "2005-2006"
                    and (enrollment.dropout_date < "1900-01-01"
                       or enrollment.dropout_date > "2006-01-06"
                        or enrollment.dropout_date is null)
                    group by families.family_id
                    order by families.name) as enrolled
on enrolled.family_id = parents.family_id
where workers.school_year = "2005-2006"
group by  workday
order by  workday


-- any  do not contacts made their way in here?
select *
from invitations 
left join leads using (lead_id)
where invitations.school_year = '2005-2006' 
and (leads.do_not_contact is not null or leads.do_not_contact > '2000-01-01') 
and leads.do_not_contact < now();

--- migrate last years sponsorships forward
insert into sponsorship_types 
(sponsorship_name, sponsorship_description, sponsorship_price, school_year) 
select sponsorship_name, sponsorship_description, sponsorship_price, '2005-2006'
from sponsorship_types 
where school_year = '2004-2005';


-- label query
SELECT *, 
concat_ws("\n", 
    companies.company_name, 
    if((length (companies.first_name) + length(companies.last_name)) > 1, 
        concat_ws(" ", companies.first_name, companies.last_name), 
        null), 
    if(length(companies.address1) > 1, companies.address1, NULL), 
    if(length(companies.address2) > 1, companies.address2, NULL), 
    if(length(companies.city) > 1, companies.city, NULL), 
    if (length(companies.phone) > 1, companies.phone, NULL), 
    if(length (companies.email_address) > 1, companies.email_address, NULL)) 
    as company_label , 
companies.company_id as SAFE_company_id 
FROM companies 
WHERE company_name like 'A%' 
GROUP BY companies.company_id , companies.company_id 
ORDER BY companies.company_name,companies.last_name,companies.first_name ;



-------- parent ed attendance summary
select distinct enrolled.name, 
count(distinct calendar_events.calendar_event_id) - round(hours/3) as missed_meetings,
count(distinct calendar_events.calendar_event_id) as meetings_required,
hours/3 as meetings_attended
from calendar_events,
(select distinct families.family_id, families.name, start_date, dropout_date
                    from families
                        left join kids on families.family_id = kids.family_id 
                        left join enrollment on kids.kid_id = enrollment.kid_id 
                    where enrollment.school_year = "2005-2006"
                    and (enrollment.dropout_date < "1900-01-01"
                       or enrollment.dropout_date > "2006-01-06"
                        or enrollment.dropout_date is null)
                    group by families.family_id
                    order by families.name) as enrolled
left join (select sum(hours) as hours, family_id
     from  parent_ed_attendance
     left join parents    on parent_ed_attendance.parent_id = parents.parent_id
     left join calendar_events 
        on parent_ed_attendance.calendar_event_id = calendar_events.calendar_event_id
     where school_year = '2005-2006'
     group by family_id) as attended
on attended.family_id = enrolled.family_id
where school_year = '2005-2006' and event_id = 2
and event_date >= start_date and  
    (event_date <= dropout_date or dropout_date is null)
and event_date <= now()
group by enrolled.family_id
order by enrolled.name
;




----
SELECT auction_donation_items.quantity ,
auction_donation_items.short_description , _cache_item_description , 
'ERROR! use _cache_item_description field instead' as item_description ,
auction_donation_items.item_value , auction_donation_items.item_type ,
auction_donation_items.date_received , 
auction_donation_items.location_in_garage ,
auction_donation_items.school_year ,
auction_donation_items.auction_donation_item_id ,
auction_donation_items.thank_you_id ,
auction_donation_items.auction_donation_item_id as SAFE_auction_donation_item_id
FROM auction_donation_items 
LEFT JOIN auction_items_families_join ON
auction_items_families_join.auction_donation_item_id=
auction_donation_items.auction_donation_item_id 
WHERE school_year = "2005-2006"
GROUP BY auction_donation_items.auction_donation_item_id 
ORDER BY auction_donation_items.short_description
\G

---this works but simpletable hates it. i don't know why
select concat_ws("\n", companies.company_name, 
if(length(companies.first_name) + length(companies.last_name) > 1, 
concat_ws(" ", companies.first_name, companies.last_name), null), 
if(length(companies.address1) > 1, companies.address1, NULL), 
if(length(companies.address2) > 1, companies.address2, NULL), 
if(length(companies.city) > 1, companies.city, NULL), 
if(length(companies.phone) > 1, companies.phone, NULL), 
if(length(companies.email_address) > 1, companies.email_address, NULL)) 
as company_label, 
companies.company_id, sum(inc.payment_amount) as cash_donations, 
sum(pur.payment_amount) as auction_purchases, 
sum(auct.item_value) as auction_donations, 
sum(iks.item_value) as in_kind_donations 
from companies 
left join (select sum(item_value) as item_value, company_id 
from companies_auction_join as caj 
left join auction_donation_items as adi 
on caj.auction_donation_item_id = adi.auction_donation_item_id 
where school_year like '2005-2006' 
group by caj.company_id) as auct 
on auct.company_id = companies.company_id 
left join (select sum(item_value) as item_value, company_id 
from companies_in_kind_join as cikj 
left join in_kind_donations as ikd 
on cikj.in_kind_donation_id = ikd.in_kind_donation_id 
where school_year like '2005-2006' 
group by cikj.company_id) as iks 
on iks.company_id = companies.company_id 
left join (select sum(payment_amount) as payment_amount, company_id 
from companies_income_join as cinj 
left join income on cinj.income_id = income.income_id 
where school_year like '2005-2006' 
group by cinj.company_id) as inc 
on inc.company_id = companies.company_id 
left join (select payment_amount, company_id 
from springfest_attendees as atd 
left join auction_purchases as ap 
on ap.springfest_attendee_id = atd.springfest_attendee_id 
left join income on ap.income_id = income.income_id 
where income.school_year like '2005-2006' 
group by atd.company_id) as pur 
on pur.company_id = companies.company_id 
group by companies.company_id 
having cash_donations > 0 or auction_purchases > 0 or auction_donations > 0 
or in_kind_donations > 0 
order by cash_donations desc, auction_purchases desc, 
auction_donations desc, in_kind_donations desc



---- aha. having... means it only shows those who bought stuff.

select concat_ws("\n", companies.company_name, 
if(length(companies.first_name) + length(companies.last_name) > 1, 
concat_ws(" ", companies.first_name, companies.last_name), null), 
if(length(companies.address1) > 1, companies.address1, NULL), 
if(length(companies.address2) > 1, companies.address2, NULL), 
if(length(companies.city) > 1, companies.city, NULL), 
if(length(companies.phone) > 1, companies.phone, NULL), 
if(length(companies.email_address) > 1, companies.email_address, NULL)) 
as company_label, 
companies.company_id, sum(inc.payment_amount) as cash_donations, 
sum(pur.payment_amount) as auction_purchases, 
sum(auct.item_value) as auction_donations, 
sum(iks.item_value) as in_kind_donations 
from companies 
left join (select sum(item_value) as item_value, company_id 
from companies_auction_join as caj 
left join auction_donation_items as adi 
on caj.auction_donation_item_id = adi.auction_donation_item_id 
where school_year like '%' 
group by caj.company_id) as auct 
on auct.company_id = companies.company_id 
left join (select sum(item_value) as item_value, company_id 
from companies_in_kind_join as cikj 
left join in_kind_donations as ikd 
on cikj.in_kind_donation_id = ikd.in_kind_donation_id 
where school_year like '%' 
group by cikj.company_id) as iks 
on iks.company_id = companies.company_id 
left join (select sum(payment_amount) as payment_amount, company_id 
from companies_income_join as cinj 
left join income on cinj.income_id = income.income_id 
where school_year like '%' 
group by cinj.company_id) as inc 
on inc.company_id = companies.company_id 
left join (select payment_amount, company_id 
from springfest_attendees as atd 
left join auction_purchases as ap 
on ap.springfest_attendee_id = atd.springfest_attendee_id 
left join income on ap.income_id = income.income_id 
where income.school_year like '%' 
group by atd.company_id) as pur 
on pur.company_id = companies.company_id 
where companies.territory_id = 12
group by companies.company_id 
order by cash_donations desc, auction_purchases desc, 
auction_donations desc, in_kind_donations desc

--- delete some number of random nobodies
update invitations 
join
(select leads.lead_id
from invitations
left join leads on invitations.lead_id = leads.lead_id
left join tickets
on invitations.lead_id = tickets.lead_id
left join leads_income_join
on invitations.lead_id = leads_income_join.lead_id
left join sponsorships
on invitations.lead_id = sponsorships.lead_id
left join companies
on leads.company_id = companies.company_id
left join ads
on invitations.lead_id = ads.lead_id
left join springfest_attendees
on invitations.lead_id = springfest_attendees.lead_id
left join kids
on invitations.lead_id = kids.doctor_id
where  invitations.school_year = '2005-2006'
and leads.source_id = 2
and (label_printed is not null and label_printed > "1000-01-01")
and (invitations.family_id is null or invitations.family_id < 1)
and tickets.lead_id is null 
and ads.lead_id is null
and sponsorships.lead_id is null
and leads_income_join.lead_id is null
and leads.company_id is null
and springfest_attendees.lead_id is null
and kids.doctor_id is null
order by rand()
limit 100) as deadbeats
on deadbeats.lead_id = invitations.lead_id
and  invitations.school_year = '2005-2006'
set invitations.label_printed = NULL
;

--- ugly donors query:
-- everyone who donated something but DID NOT buy an ad or a sponsorship!
select distinct companies.*
from companies
left join companies_auction_join 
on companies_auction_join.company_id = companies.company_id 
left join auction_donation_items 
on companies_auction_join.auction_donation_item_id = auction_donation_items.auction_donation_item_id
and auction_donation_items.school_year = '2005-2006'
left join companies_income_join 
on companies_income_join.company_id = companies.company_id
left join income
on companies_income_join.income_id = income.income_id
and income.school_year = '2005-2006'
left join companies_in_kind_join 
on companies_in_kind_join.company_id = companies.company_id
left join in_kind_donations
on companies_in_kind_join.in_kind_donation_id = in_kind_donations.in_kind_donation_id
and in_kind_donations.school_year = '2005-2006'
left join sponsorships on sponsorships.company_id = companies.company_id
left join ads on ads.company_id = companies.company_id
where
(income.payment_amount > 0
or auction_donation_items.item_value > 0
or in_kind_donations.item_value > 0)
and ads.ad_id is null
and sponsorships.sponsorship_id is null
order by if(companies.listing, companies.listing, companies.company_name), companies.last_name
\G


----- auction summaries
select sum(if(auction_items_families_join.auction_donation_item_id is null, 0, 
item_value)) as family_auction, 
sum(if(companies_auction_join.auction_donation_item_id is null, 0, 
item_value)) as solicitation_auction, sum(item_value) as total_auction, 
school_year
from auction_donation_items
left join companies_auction_join
on companies_auction_join.auction_donation_item_id = 
auction_donation_items.auction_donation_item_id
left join auction_items_families_join
on auction_items_families_join.auction_donation_item_id = 
auction_donation_items.auction_donation_item_id
where school_year = '2005-2006'
group by school_year
;

------- packages
select 'Non-Cash Estimated Package \"Value\"', 
sum(package_value) as Package_Estimate
from packages
where school_year = '$schoolyear'
group by school_year
;


--- tickets by source
select  invitations.relation,
sum(if(invitations.family_id>0,0,ticket_quantity)) as Alumni_List ,
sum(if(invitations.family_id>0,ticket_quantity,0)) as Family_Supplied ,
sum(ticket_quantity)  as Total 
from tickets
left join invitations 
on invitations.lead_id = tickets.lead_id
and invitations.school_year  = '2004-2005'
where tickets.lead_id is not null 
and tickets.ticket_quantity > 0
and tickets.school_year = '2004-2005'
group by invitations.relation
order by total desc
;

-----ticket income by source
select  invitations.relation,
    sum(if(invitations.family_id>0,0,payment_amount)) as Alumni_List ,
    sum(if(invitations.family_id>0,payment_amount,0)) as Family_Supplied ,
    sum(payment_amount)  as Total 
from leads_income_join
   left join invitations 
       on invitations.lead_id = leads_income_join.lead_id
       and invitations.school_year  = "2005-2006"
   left join income on income.income_id = leads_income_join.income_id
where leads_income_join.lead_id is not null 
     and income.school_year = "2005-2006"
group by invitations.relation
order by total desc
;

-- RSVP income by source
select  invitations.relation,
    sum(if(invitations.family_id>0,0,payment_amount)) as Alumni_List ,
    sum(if(invitations.family_id>0,payment_amount,0)) as Family_Supplied ,
    sum(payment_amount)  as Total 
from tickets
   left join invitations 
       on invitations.lead_id = tickets.lead_id
       and invitations.school_year  = "2005-2006"
   left join income on income.income_id = tickets.income_id
where tickets.lead_id is not null 
     and tickets.ticket_quantity > 0
     and tickets.school_year = "2005-2006"
group by invitations.relation
order by total desc
;

--- both
select  invitations.relation,
    sum(if(invitations.family_id>0,0,payment_amount)) as Alumni_List ,
    sum(if(invitations.family_id>0,payment_amount,0)) as Family_Supplied ,
    sum(payment_amount)  as Total 
from invitations
   left join leads_income_join
       on invitations.lead_id = leads_income_join.lead_id
       and invitations.school_year  = "2005-2006"
   left join tickets
       on invitations.lead_id = tickets.lead_id
       and invitations.school_year  = "2005-2006"
   left join income on income.income_id = tickets.income_id or leads_income_join.income_id = income.income_id
where income.school_year = "2005-2006" 
    and 
    (leads_income_join.lead_id is not null 
        or 
    (tickets.lead_id is not null 
        and tickets.ticket_quantity > 0
        and tickets.school_year = "2005-2006"))
group by invitations.relation
order by total desc
;


--- find orphans
select auction_donation_items.auction_donation_item_id,
    packages.package_id, auction_packages_join.package_id 
from auction_packages_join
left join auction_donation_items
    on auction_donation_items.auction_donation_item_id = 
        auction_packages_join.auction_donation_item_id 
left join packages on packages.package_id = auction_packages_join.package_id 
where packages.package_id is null;

-- unneeded
 select * from auction_packages_join where auction_donation_item_id is null or auction_donation_item_id < 1 or package_id is null or package_id < 1;



--- update packages for new thing (one time only)
update packages 
left join package_types 
    on packages.package_type = package_types.package_type_short 
set packages.package_type_id = package_types.package_type_id


--- cute little utility to whack bad addresses
update leads 
set do_not_contact = now() where lead_id in 
(759,960,698,604)
;


-- check for squashed alumni
select leads.lead_id, last_name from leads 
left join invitations on leads.lead_id = invitations.lead_id
    and invitations.school_year = '2005-2006'
where (source_id = 2 or source_id = 7) and invitations.lead_id is null
and (do_not_contact is not null and do_not_contact > "1000-01-01")
order by leads.last_name
;


--- THERE IS A REASON WHY THOSE WERE DELETED!
--- whack them all
create temporary table dead (
    lead_id int(32) not null unique
);
insert into dead
(select distinct invitations.lead_id 
from invitations
left join leads 
    on leads.lead_id = invitations.lead_id
where (do_not_contact is not null and do_not_contact > "1000-01-01") 
    and school_year = "2005-2006"
    and (label_printed is null or label_printed < "1000-01-01"))
;
delete
from invitations 
where invitations.lead_id  in
(select * from dead)
;

-- periodically
update invitations 
set label_printed = "2006-02-17 17:00" 
where (label_printed is null or label_printed < "1000-01-01") 
    and school_year = "2005-2006";


-- sponsors, sorted and formatted

select 
    if(companies.listing is not null and companies.listing > "", 
        companies.listing, 
        if(coalesce(companies.company_name, leads.company) is not null 
            and coalesce(companies.company_name, leads.company) > "", 
            coalesce(companies.company_name, leads.company), 
            concat_ws(" ", coalesce(leads.first_name, companies.first_name), 
        coalesce(leads.last_name, companies.last_name)))) 
      as sponsor_formatted, 
sponsorship_types.sponsorship_price 
from sponsorships 
    left join sponsorship_types 
        on sponsorship_types.sponsorship_type_id = 
            sponsorships.sponsorship_type_id 
    left join companies on companies.company_id = sponsorships.company_id 
    left join leads on leads.lead_id = sponsorships.lead_id 
where sponsorships.school_year = "2005-2006" 
order by sponsorship_price desc, 
    if(companies.listing is not null and companies.listing > "", 
        companies.listing, 
        if(coalesce(companies.company_name, leads.company) is not null
            and coalesce(companies.company_name, leads.company) > "", 
            coalesce(companies.company_name, leads.company), 
        coalesce(leads.last_name, companies.last_name))) asc
;


set @num=0;
update packages set package_number=(@num:=@num+1) 
where package_type_id = 2 and school_year = "2005-2006"
ORDER BY package_number
;


--- the count. blah! blah!
select school_year, count(springfest_attendee_id) as count 
from springfest_attendees 
where attended = 'Yes'  
group by school_year 
order by school_year
;

-- USELESS
select ticket_type.description as Ticket_Type, paddle_number,
    springfest_attendee_id as Total, springfest_attendees.school_year
from springfest_attendees 
    left join tickets 
        on springfest_attendees.ticket_id = tickets.ticket_id
    left join ticket_type 
        on tickets.ticket_type_id = ticket_type.ticket_type_id
where springfest_attendees.school_year = '2005-2006'
order by  springfest_attendees.paddle_number
;

--- TEMP paddle test with criteria
select springfest_attendees.springfest_attendee_id,
springfest_attendees.paddle_number, ticket_summary.vip_flag, coalesce(leads.first_name, companies.first_name, parents.first_name) as first_name, 
coalesce(leads.last_name, companies.last_name, parents.last_name) as last_name, 
coalesce(leads.company, companies.company_name) as company_name, 
coalesce(leads.address1, companies.address1, families.address1) as address1, 
coalesce(leads.address2, companies.address2) as address2, 
coalesce(leads.city, companies.city) as city, 
coalesce(leads.state, companies.state) as state, 
coalesce(leads.zip, companies.zip) as zip, 
coalesce(leads.phone, companies.phone, families.phone) as phone, 
coalesce(leads.email_address, companies.email_address, families.email) as email_address, 
ticket_summary.ticket_purchaser, 
truncate (income.payment_amount / ticket_summary.ticket_quantity,2) as payment_amount, 
springfest_attendees.attended, 
coalesce(parents.family_id, ticket_summary.family_id, companies.family_id) as family_id, 
springfest_attendees.school_year 
from springfest_attendees 
left join leads on springfest_attendees.lead_id = leads.lead_id 
left join companies on springfest_attendees.company_id = companies.company_id 
left join parents on springfest_attendees.parent_id = parents.parent_id 
left join families on parents.family_id = families.family_id 
left join 
    (select tickets.ticket_id, tickets.vip_flag, tickets.income_id, 
        tickets.school_year, tickets.ticket_quantity, 
        concat_ws(' ', coalesce(leads.first_name, companies.first_name) , 
        coalesce(leads.last_name, companies.last_name, 
                concat(families.name, ' Family')), 
        coalesce(leads.company, companies.company_name), 
        coalesce(leads.address1, companies.address1, families.address1), 
        coalesce(leads.address2, companies.address2), 
        coalesce(leads.city, companies.city), 
        coalesce(leads.state, companies.state), 
        coalesce(leads.zip, companies.zip), 
        coalesce(leads.phone, companies.phone, families.phone), 
        coalesce(leads.email_address, companies.email_address, families.email)) as ticket_purchaser, 
        coalesce(leads.first_name, companies.first_name) as first, 
        coalesce(leads.last_name, companies.last_name, 
            concat(families.name, ' Family')) as last, 
        tickets.family_id
    from tickets 
        left join leads on tickets.lead_id = leads.lead_id 
        left join companies on tickets.company_id = companies.company_id 
        left join families on tickets.family_id = families.family_id ) as ticket_summary on ticket_summary.ticket_id = springfest_attendees.ticket_id 
        left join income on ticket_summary.income_id = income.income_id 
where springfest_attendees.school_year = '2005-2006' and 
    (springfest_attendees.parent_id > 0 
        or springfest_attendees.company_id > 0 
        or springfest_attendees.lead_id > 0) 
order by coalesce(leads.last_name, companies.last_name, parents.last_name, 
            ticket_summary.last), 
        coalesce(leads.first_name, companies.first_name, parents.first_name, 
                ticket_summary.first) 
\G


--- TEMP paddle test1
select springfest_attendees.*
from springfest_attendees 
left join leads on springfest_attendees.lead_id = leads.lead_id 
left join companies on springfest_attendees.company_id = companies.company_id 
left join parents on springfest_attendees.parent_id = parents.parent_id 
left join families on parents.family_id = families.family_id 
left join 
    (select tickets.ticket_id, tickets.vip_flag, tickets.income_id, 
        tickets.school_year, tickets.ticket_quantity, 
        concat_ws(' ', coalesce(leads.first_name, companies.first_name) , 
        coalesce(leads.last_name, companies.last_name, 
                concat(families.name, ' Family')), 
        coalesce(leads.company, companies.company_name), 
        coalesce(leads.address1, companies.address1, families.address1), 
        coalesce(leads.address2, companies.address2), 
        coalesce(leads.city, companies.city), 
        coalesce(leads.state, companies.state), 
        coalesce(leads.zip, companies.zip), 
        coalesce(leads.phone, companies.phone, families.phone), 
        coalesce(leads.email_address, companies.email_address, families.email)) as ticket_purchaser, 
        coalesce(leads.first_name, companies.first_name) as first, 
        coalesce(leads.last_name, companies.last_name, 
            concat(families.name, ' Family')) as last, 
        tickets.family_id, tickets.company_id, tickets.lead_id
    from tickets 
        left join leads on tickets.lead_id = leads.lead_id 
        left join companies on tickets.company_id = companies.company_id 
        left join families on tickets.family_id = families.family_id ) as ticket_summary on ticket_summary.ticket_id = springfest_attendees.ticket_id 
        left join income on ticket_summary.income_id = income.income_id 
where springfest_attendees.school_year = '2005-2006' and 
    (springfest_attendees.parent_id > 0 )
order by coalesce(leads.last_name, companies.last_name, parents.last_name, 
            ticket_summary.last), 
        coalesce(leads.first_name, companies.first_name, parents.first_name, 
                ticket_summary.first) 
\G


 
-- day of stuff
                       select chart_of_accounts.description as Description, 
       sum(if(date_format(income.check_date, '%Y-%m-%d') not like '2006-03-25' ,
                income.payment_amount,0)) 
            as Before_After_Event ,
       sum(if(date_format(income.check_date, '%Y-%m-%d') like '2006-03-25' ,
                income.payment_amount,0)) 
            as Day_Of_Event ,
       sum(payment_amount)  as Total 
        from income 
                           left join chart_of_accounts 
                        on income.account_number = 
                                chart_of_accounts.account_number 
   where income.school_year = '2005-2006'
        group by income.account_number order by total desc
;


-- quick and dirty "attended" thing.
-- make into either an interactive, or a checkbox, goddammit.
update springfest_attendees set attended = 'Yes' where
school_year = '2005-2006' and
paddle_number in
(200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 242, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 99, 98, 100, 101, 133, 102, 103, 105, 106, 107, 109, 108, 110, 111, 112, 116, 117, 119, 118, 120, 121, 123, 122, 127, 126, 128, 120, 130, 174, 132, 134, 135, 137, 136, 141, 143, 142, 145, 147, 149, 148, 150, 151, 131, 152, 153, 162, 163, 165, 164, 166, 173, 172, 177, 178, 179, 181, 182, 183, 184, 185, 186, 187, 125, 113, 189, 188, 190, 159, 193, 192, 195, 197, 198, 354, 355, 320, 321, 74, 75, 358, 10, 324, 325, 318, 319, 66, 67, 322, 323, 13, 78, 79, 24, 25, 83, 22, 337, 56, 57, 292, 293, 11, 12, 26, 27, 49, 48, 72, 73, 338, 60, 61, 70, 71, 316, 317, 1, 2, 50, 51, 68, 69, 326, 327, 84, 85, 86, 87, 80, 81, 250, 251, 88, 89, 52, 53, 255, 256, 257, 76, 77, 329, 330, 252, 255, 350, 351, 64, 65, 356, 357, 3, 4, 181, 182, 183, 185, 116, 159, 106, 107, 112, 120, 121, 132, 152, 153, 157, 156, 158, 166, 165);


-- balloon hack
insert into auction_purchases 
set springfest_attendee_id = 792, income_id = 717, package_sale_price = <whatever>



--- votecounts
select questions.question, answers.answer, 
         count(votes.answer_id) as votecount 
from votes
left join answers on votes.answer_id = answers.answer_id
left join questions on questions.question_id = votes.question_id 
group by answers.answer_id
order by questions.school_year asc, questions.question asc, votecount desc
;


-- non-voting families
select enrolled.*, this_question.question_id from
(select distinct  families.*
   from families
       left join kids on families.family_id = kids.family_id 
       left join enrollment on kids.kid_id = enrollment.kid_id 
   where enrollment.school_year = '2005-2006'
   and ((enrollment.dropout_date < '1900-01-01'
       or enrollment.dropout_date is null)
       or enrollment.dropout_date > now())
   group by families.family_id
   order by families.name) as enrolled
left join  
(select * from votes where question_id = 1) as this_question
on this_question.family_id = enrolled.family_id
where this_question.question_id is null
\G


--- some samples
set @school_year := '2005-2006';
set @ticket_price := 30;
set @ad_text := 'ad valued at';
set @ticket_text := 'to Springfest valued at';
set @cash_text := 'cash';


---updated attendee query
select ticket_type.description as Ticket_Type, 
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
                and invitations.school_year = '2005-2006'
                where springfest_attendees.school_year = '2005-2006'
                     and attended = 'Yes'
                group by tickets.ticket_type_id
                order by ticket_type.description
\G

--- EOF