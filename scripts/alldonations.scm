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
   "select companies.company_name, companies.address1, companies.city,
		companies.state, companies.state, companies.zip, companies.phone,
		companies.email,
        sum(auction.amount)  as auction_item_total 
    from auction
        left join companies_auction_join
               on auction.auctionid = companies_auction_join.auctionid
            left join companies 
                on companies.company_id = 
                    companies_auction_join.company_id
    where companies.company_id is not null and companies.do_not_contact is null
    group by  companies.company_id 
    order by auction_item_total desc, companies.company_name asc"

"select coalesce(companies.company_name, 
        concat_ws(' ', leads.first, leads.last, leads.company)) as donor,
		concat_ws(' ', leads.addr, companies.address1) as address,
		concat_ws(' ', leads.addrcont, companies.address2) as address2,
		concat_ws(' ', leads.city, companies.city) as city,
		concat_ws(' ', leads.state, companies.state) as state,
		concat_ws(' ', leads.zip, companies.zip) as zip,
		concat_ws(' ', leads.phone, companies.phone) as phone,
		companies.email,
		      sum(inc.amount)  as cash_total 
    from inc 
        left join companies_income_join
               on inc.incid = companies_income_join.incid
            left join companies 
                on companies.company_id = 
                    companies_income_join.company_id
        left join invitation_rsvps 
            on invitation_rsvps.incid = inc.incid 
            left join leads on invitation_rsvps.leadsid = leads.leadsid
    where leads.leadsid is not null or companies.company_id is not null
    group by leads.leadsid, companies.company_id 
    order by cash_total desc, 
        leads.last asc, leads.first asc, companies.company_name asc"
   
))

;; the corporate auctions
(define *dbh* (apply simplesql-open "mysql"
				   (read-conf "/mnt/kens/ki/proj/coop/sql/db-input.conf")))



(map (lambda (query) (list-to-tab-delim (simplesql-query *dbh* query )))
	 queries) 


(simplesql-close *dbh*)

;;EOF