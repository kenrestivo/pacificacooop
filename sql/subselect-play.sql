-- $Id$
-- for playing with subselects


-- first step
 select left(company_name, 20) as name, 
      sum(auction_donation_items.item_value) as auction_donations,
        sum(in_kind_donations.item_value) as in_kind_donations
      from companies
          left join companies_auction_join 
              on companies_auction_join.company_id = companies.company_id
          left join auction_donation_items 
              on companies_auction_join.auction_donation_item_id = 
                auction_donation_items.auction_donation_item_id 
          left join companies_in_kind_join
                on companies_in_kind_join.company_id = companies.company_id
         left join in_kind_donations
                on in_kind_donations.in_kind_donation_id =
                    companies_in_kind_join.in_kind_donation_id 
    where companies.company_id = 82
           group by companies.company_id      
	having  auction_donations > 0 
        or in_kind_donations > 0
     order by   auction_donations desc,
        in_kind_donations desc, companies.company_name asc;


-- back another level
 select left(company_name, 20) as name, 
      auction_donation_items.item_value as auction_donations,
        in_kind_donations.item_value as in_kind_donations
      from companies
          left join companies_auction_join 
              on companies_auction_join.company_id = companies.company_id
          left join auction_donation_items 
              on companies_auction_join.auction_donation_item_id = 
                auction_donation_items.auction_donation_item_id 
          left join companies_in_kind_join
                on companies_in_kind_join.company_id = companies.company_id
         left join in_kind_donations
                on in_kind_donations.in_kind_donation_id =
                    companies_in_kind_join.in_kind_donation_id 
    where companies.company_id = 82
      order by   auction_donations desc,
        in_kind_donations desc, companies.company_name asc;

-- the union of the snake
 (select left(company_name, 20) as name, 
      auction_donation_items.item_value as auction_donations
        from companies
          left join companies_auction_join 
              on companies_auction_join.company_id = companies.company_id
          left join auction_donation_items 
              on companies_auction_join.auction_donation_item_id = 
                auction_donation_items.auction_donation_item_id 
    where companies.company_id = 82
      order by   auction_donations desc)
union
 (select left(company_name, 20) as name, 
        in_kind_donations.item_value as in_kind_donatio
          left join companies_in_kind_join
                on companies_in_kind_join.company_id = companies.company_id
         left join in_kind_donations
                on in_kind_donations.in_kind_donation_id =
                    companies_in_kind_join.in_kind_donation_id 
    where companies.company_id = 82
      order by   
        in_kind_donatio desc)h


-- simple subselect
select distinct left(company_name, 20) as name, 
        auct.item_value as auction_donations,
        iks.item_value as in_kind_donations
from companies
left join 
    (select  item_value, company_id
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id) 
    as auct
        on auct.company_id = companies.company_id
        and auct.item_value is not null
left join 
    (select  item_value, company_id
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id) 
    as iks
        on iks.company_id = companies.company_id
            and iks.item_value is not null
where companies.company_id = 82;
 
