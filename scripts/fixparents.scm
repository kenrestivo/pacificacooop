;;; $Id$
;; put the parentid in for all the solicitation auction and money
;; NOTE! this is NOT USED! this is a dead-end
;; (load "/mnt/kens/ki/proj/coop/scripts/fixparents.scm")

(use-modules (ice-9 slib)
			 (kenlib)
			 (database simplesql))
(require 'printf)


(define *dbh* (apply simplesql-open "mysql"
					 (read-conf "/mnt/kens/ki/proj/coop/sql/db-input.conf")))


(for-each
 (lambda (table)
   (for-each 
	(lambda (record)
	  (safe-sql *dbh*
				(sprintf #f "update %s set parent_id = %d where family_id = %d"
						 table
						 (vector-ref record 0)
						 (vector-ref record 1)
						 )))
	
	(cdr (simplesql-query *dbh*
						  (sprintf #f
		    "select parents.parent_id, parents.family_id
				from %s
					left join parents
 	  				on parents.family_id = %s.family_id
				and parents.type = 'mom'
			group by parents.family_id
			order by parents.family_id"
			table table)))))
 '("companies_auction_join"
   "companies_income_join"
   "companies_in_kind_join"))

(simplesql-close *dbh*)

;;; EOF
