-- $Id$
--  massive thankyou needed report

select concat_ws(' - ', company_name, concat_ws(' ', first_name, last_name)) 
    as Company, 
        companies.company_id as id, 'company_id' as id_name,
        coalesce(sum(inc.payment_amount),0) +
        coalesce(sum(tic.payment_amount),0) + 
        coalesce(sum(auct.item_value),0) + 
        coalesce(sum(iks.item_value),0) 
        as Total,
   concat_ws('\n', 
        if(coalesce(sum(inc.payment_amount),0) > 0,
            concat('$', coalesce(sum(inc.payment_amount),0), ' cash'), null),
auct.auction_items, iks.in_kind_items) as items,
concat_ws(' ', 
    if(companies.salutation is not null and companies.salutation > "",
            companies.salutation, companies.first_name), 
            companies.last_name) as dear,
concat_ws("\n"
,concat_ws(" " , if(companies.salutation > "", companies.salutation, null), companies.first_name, companies.last_name)
,if(length(companies.title)>0, companies.title, null)
,if(length(companies.company_name)>0, companies.company_name, null)
,if(length(companies.address1)>0, companies.address1, null)
,if(length(companies.address2)>0, companies.address2, null)
,concat_ws(" ", concat(companies.city, ", ", companies.state), companies.zip, 
    if(companies.country != "USA", companies.country, ""))) as address_label,
inc.income_ids, auct.auction_donation_item_ids, iks.in_kind_donation_ids
from companies
left join 
    (select  sum(item_value) as item_value, company_id,
    group_concat(adi.short_description, '\n') 
        as auction_items,
    group_concat(adi.auction_donation_item_id) as auction_donation_item_ids
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year = '2005-2006' 
        and adi.date_received > '2000-01-01'
        and adi.thank_you_id is null
        group by caj.company_id) 
    as auct
        on auct.company_id = companies.company_id
left join 
    (select  sum(item_value) as item_value, company_id,
    group_concat(ikd.item_description, '\n') 
        as in_kind_items,
    group_concat(ikd.in_kind_donation_id) as in_kind_donation_ids
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year = '2005-2006'
        and ikd.date_received > '2000-01-01'
        and ikd.thank_you_id is null
        group by cikj.company_id) 
    as iks
        on iks.company_id = companies.company_id
left join 
    (select  sum(payment_amount) as payment_amount, company_id,
        group_concat(income.income_id) as income_ids
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '2005-2006'  
        and income.thank_you_id is null
        group by cinj.company_id) 
    as inc
        on inc.company_id = companies.company_id
group by companies.company_id
having Total > 0
UNION DISTINCT
select concat_ws(' - ', concat_ws(' ', first_name, last_name), company ) 
    as Company, 
        leads.lead_id as id, 'lead_id' as id_name,
        coalesce(sum(tic.payment_amount),0) + 
                coalesce(sum(inc.payment_amount),0) as Total,
        if(coalesce(sum(inc.payment_amount),0) > 0,
            concat('$', coalesce(sum(inc.payment_amount),0), ' cash'), null) as items,
concat_ws(' ', if(leads.salutation is not null and leads.salutation > "",
            leads.salutation, leads.first_name), 
            leads.last_name) as dear,
concat_ws("\n"
,concat_ws(" " , if(leads.salutation > "", leads.salutation, null), 
    leads.first_name, leads.last_name)
,if(length(leads.title)>0, leads.title, null)
,if(length(leads.company)>0, leads.company, null)
,if(length(leads.address1)>0, leads.address1, null)
,if(length(leads.address2)>0, leads.address2, null)
,concat_ws(" ", concat(leads.city, ", ", leads.state), leads.zip, 
    if(leads.country != "USA", leads.country, ""))) as address_label,
concat_ws(',', inc.income_ids, tic.income_ids) as income_ids,
"" as auction_donation_item_ids, "" as in_kind_donation_ids
from leads
left join 
    (select lead_id, sum(payment_amount) as payment_amount,
        group_concat(income.income_id) as income_ids
     from leads_income_join as linj 
     left join income 
              on linj.income_id = 
                income.income_id
        where income.school_year = '2005-2006' 
        and income.thank_you_id is null
        group by linj.lead_id) 
    as inc
        on leads.lead_id = inc.lead_id
left join 
    (select lead_id, sum(payment_amount) as payment_amount,
    group_concat(income.income_id) as income_ids
     from tickets
     left join income 
              on tickets.income_id = 
                income.income_id
        where income.school_year = '2005-2006' 
        and income.thank_you_id is null
        group by tickets.lead_id) 
    as tic 
        on tic.lead_id = leads.lead_id
group by leads.lead_id
having Total > 0
order by Company
\G
