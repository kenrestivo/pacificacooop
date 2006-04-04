-- $Id$

--- narsty recoverexisting query
--- as bad as this is, though, it's MUCH faster than doing it in PHP!
--- and about 1/2 the lines of code!


select distinct thank_you.*,
DATE_FORMAT(thank_you.date_sent,"%W, %M %e, %Y") as date_sent_fmt,
coalesce(auction_summary.school_year, in_kind_summary.school_year,
    income_summary.school_year) as school_year,
concat_ws("\n", coalesce(leads.company, companies.company_name), 
    concat_ws(" ", coalesce(leads.first_name, companies.first_name),
        coalesce(leads.last_name, companies.last_name))) as recipient,
concat_ws(" ", working_parents.first_name, working_parents.last_name) 
    as salesperson,
concat_ws("\n", concat("$", income_summary.total_payment, @cash_text),
            auction_summary.short_descriptions, 
            in_kind_summary.item_descriptions) as items
from thank_you
left join (select group_concat(auction_donation_items.short_description, "\n")
                    as short_descriptions, 
                auction_donation_items.thank_you_id,
                auction_donation_items.school_year,
                companies_auction_join.family_id, 
                companies_auction_join.company_id
            from auction_donation_items 
                left join companies_auction_join 
                    on companies_auction_join.auction_donation_item_id = 
                        auction_donation_items.auction_donation_item_id
            group by auction_donation_items.thank_you_id) as auction_summary
       on thank_you.thank_you_id = auction_summary.thank_you_id
left join (select group_concat(in_kind_donations.item_description, "\n")
                    as item_descriptions,
                in_kind_donations.thank_you_id,
                in_kind_donations.school_year,
                companies_in_kind_join.family_id, 
                companies_in_kind_join.company_id
            from in_kind_donations
            left join companies_in_kind_join 
                on companies_in_kind_join.in_kind_donation_id = 
                    in_kind_donations.in_kind_donation_id
            group by in_kind_donations.thank_you_id) as in_kind_summary 
    on thank_you.thank_you_id = in_kind_summary.thank_you_id
left join (select sum(income.payment_amount) as total_payment,
            income.thank_you_id, income.school_year,
             coalesce(leads_income_join.lead_id, tickets.lead_id) as lead_id,
            companies_income_join.company_id, companies_income_join.family_id
            from income 
              left join companies_income_join 
                  on companies_income_join.income_id = income.income_id
              left join leads_income_join 
                    on leads_income_join.income_id = income.income_id
            left join tickets on tickets.income_id = income.income_id
            group by income.thank_you_id)
            as income_summary
    on thank_you.thank_you_id = income_summary.thank_you_id
left join companies 
    on coalesce(income_summary.company_id, 
        auction_summary.company_id, in_kind_summary.company_id) 
            = companies.company_id
left join leads 
    on income_summary.lead_id = leads.lead_id
left join (select parents.* from parents 
            left join workers on parents.parent_id = workers.parent_id
            where workers.parent_id is not null
            group by parents.family_id) as working_parents
        on working_parents.family_id = 
        coalesce(income_summary.family_id, 
                auction_summary.family_id, 
                in_kind_summary.family_id)
where (auction_summary.school_year = @school_year 
    or in_kind_summary.school_year = @school_year 
    or income_summary.school_year = @school_year) 
order by if(coalesce(companies.company_name, leads.company) is not null
            and coalesce(companies.company_name, leads.company) > "", 
            coalesce(companies.company_name, leads.company), 
        concat(coalesce(leads.last_name, companies.last_name),
                coalesce(leads.last_name, companies.last_name)))
\G
-- for testing: and (companies.company_id  = 49 or companies.company_id = 127 or companies.company_id  = 27 or thank_you.thank_you_id = 64)
 
