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

--- show kids and enrollment
select kids.*, enrollment.* 
    from kids 
        left join enrollment using (kid_id);

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

-- the excel export report
select leads.lead_id  as responsecode
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
        ,date_format(leads.entered, '%m/%d/%Y %T') as entered
        ,families.name as familyname
    from leads
        left join families on leads.family_id = families.family_id
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
        left join auction_items_families_join on families.family_id = auction_items_families_join.family_id
        left join auction 
        on auction_items_families_join.auction_donation_item_id = auction_donation_items.auction_donation_item_id
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


--- nasty package join. the 'users' hack is to get "xxx family" w/o coding
select auction_donation_items.* , 
            coalesce(users.name, companies.company_name) as donor
        from auction 
            left join packages on auction.package_id = packages.package_id 
            left join auction_items_families_join 
        on auction_items_families_join.auction_donation_item_id = 
                auction_donation_items.auction_donation_item_id 
            left join users on users.family_id = 
                auction_items_families_join.family_id 
            left join companies_auction_join
                 on companies_auction_join.auction_donation_item_id = 
                        auction_donation_items.auction_donation_item_id
                left join companies 
                    on companies_auction_join.company_id = 
                        companies.company_id
        where auction_donation_items.package_id < 1 and date_received is not null 
            and date_received > '0000-00-00'

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
select coalesce(companies.company_name, 
        concat_ws(' ', leads.first_name, leads.last_name, leads.company))
            as company,
        sum(income.payment_amount)  as cash_total 
    from income 
        left join companies_income_join
               on income.income_id = companies_income_join.income_id
            left join companies 
                on companies.company_id = 
                    companies_income_join.company_id
        left join invitation_rsvps 
            on invitation_rsvps.income_id = income.income_id 
            left join leads on invitation_rsvps.lead_id = leads.lead_id
    where leads.lead_id is not null or companies.company_id is not null
    group by leads.lead_id, companies.company_id  
    having cash_total >= 150
    order by cash_total desc, 
        leads.last_name asc, leads.first_name asc, companies.company_name asc;

-- nasty sponsorship join to check AUCTION levels
select companies.company_name,
        sum(auction_donation_items.item_value)  as auction_item_total
		sum(in_kind_donations.item_value) as in_kind_donation_total 
    from companies
       left join companies_auction_join 
                on companies.company_id = 
                    companies_auction_join.company_id
     	   left join auction_donation_items
        	       on auction_donation_items.auction_donation_item_id =
            	        companies_auction_join.auction_donation_item_id
		left join companies_in_kind_join
				on companies_in_kind_join.company_id =
					companies.company-id
			left join in_kind_donations
				on companies_in_kind_join.in_kind_donation_id =
					in_kind_donations.in_kind_donation_id
    where companies.company_id is not null
    group by  companies.company_id having auction_item_total >= 150
    order by auction_item_total desc, companies.company_name asc


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

-- massive solicit performane query. 
----  also used to determine The people who need thankyous
 select company_name, sum(income.payment_amount) as cash_donations,
      sum(auction_donation_items.item_value) as auction_donations,
        sum(in_kind_donations.item_value) as in_kind_donations
      from companies
          left join companies_auction_join 
              on companies_auction_join.company_id = companies.company_id
          left join auction_donation_items 
              on companies_auction_join.auction_donation_item_id = 
                auction_donation_items.auction_donation_item_id
          left join companies_income_join 
              on companies_income_join.company_id = companies.company_id
          left join income 
            on companies_income_join.income_id = income.income_id    
          left join companies_in_kind_join
                on companies_in_kind_join.company_id = companies.company_id
         left join in_kind_donations
                on in_kind_donations.in_kind_donation_id =
                    companies_in_kind_join.in_kind_donation_id
        where auction_donation_items.school_year = '2004-2005'
            or income.school_year = '2004-2005'
            or in_kind_donations.school_year = '2004-2005'
    group by companies.company_id 
     order by  cash_donations desc, auction_donations desc,
        in_kind_donations desc, companies.company_name asc;

--- Eof