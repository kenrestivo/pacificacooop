;; $Id$
;; all the donations, regardless of what kind, into tab text.
;; (load "/mnt/kens/ki/proj/coop/scripts/alldonations.scm")

;; NOTE this is for the old, shitty database format,
;; where these are all separate

(use-modules (kenlib)
			 (database simplesql)
			 (ice-9 slib)
			 (srfi srfi-13)
			 )
(require 'printf)


(define queries '(

"select coalesce(companies.company_name, 
        concat_ws(' ', leads.first_name, leads.last_name, leads.company)) as donor,
		concat_ws(' ', leads.address1, companies.address1) as address1,
		concat_ws(' ', leads.address2, companies.address2) as address2,
		concat_ws(' ', leads.city, companies.city) as city,
		concat_ws(' ', leads.state, companies.state) as state,
		concat_ws(' ', leads.zip, companies.zip) as zip,
		concat_ws(' ', leads.phone, companies.phone) as phone,
		companies.email_address, leads.lead_id, companies.company_id,
		      sum(inc.amount)  as cash_total 
    from inc 
        left join companies_income_join
               on inc.income_id = companies_income_join.income_id
            left join companies 
                on companies.company_id = 
                    companies_income_join.company_id
        left join invitation_rsvps 
            on invitation_rsvps.income_id = inc.income_id 
            left join leads on invitation_rsvps.lead_id = leads.lead_id
    where leads.lead_id is not null or companies.company_id is not null
    group by leads.lead_id, companies.company_id 
    order by cash_total desc, 
        leads.last_name asc, leads.first_name asc, companies.company_name asc"

"select companies.company_name, companies.address1, companies.city,
		companies.state, companies.state, companies.zip, companies.phone,
		companies.email_address,  companies.company_id,
        sum(auction.item_value)  as auction_item_total 
    from auction
        left join companies_auction_join
               on auction.auction_donation_item_id = companies_auction_join.auction_donation_item_id
            left join companies 
                on companies.company_id = 
                    companies_auction_join.company_id
    where companies.company_id is not null and companies.do_not_contact is null
    group by  companies.company_id 
    order by auction_item_total desc, companies.company_name asc"
   
))





;;;; MAIN
(define *dbh* (apply simplesql-open "mysql"
				   (read-conf "/mnt/kens/ki/proj/coop/sql/db-input.conf")))

(define out (open-output-file "/mnt/kens/ki/proj/coop/reports/allmoney.txt"))

(map (lambda (query) (list-to-tab-delim (simplesql-query *dbh* query ) out))
	 queries)

(flush-all-ports)

(close-output-port out)

(simplesql-close *dbh*)

;;EOF
