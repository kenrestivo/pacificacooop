;;; $Id$
;; add schoolyear to columns
;; (load "/mnt/kens/ki/proj/coop/scripts/companies-to-leads.scm")

(use-modules (kenlib) (ice-9 slib)
			 (database simplesql)
			 (srfi srfi-13))
(require 'printf)

;; from-to alist, which means companies->leads
(define table-mapping
  '(("company_name" .  "company")
	("address1" . "address1")
	("address2" . "address2")
	("city" . "city")
	("state" . "state")
	("zip" . "zip")
	("country" . "country")
	("phone" . "phone")
	("fax" . "fax")
	("email_address" . "email_address")
	("territory_id" . "territory_id")
	("family_id" . "family_id") 
	("do_not_contact" . "do_not_contact")
	("flyer_ok" . "flyer_ok")))

  
(string-join (map (lambda (x) (car x)) table-mapping) ", ")

(define (get-old dbh tables old-table old-index )
  (safe-sql dbh
			(sprintf #f "select %s, %s from %s"
					 old-index
					 (string-join
					  (map (lambda (x) (car x)) tables) ", ")
					 old-table)))

(define (insert-new dbh line tables  old-table new-table old-index new-index)
  (safe-sql dbh (sprintf #f " insert into %s (%s) %s"
						 new-table 
						 (string-join
						  (map (lambda (x) (cdr x)) tables) ", ")
						 )
						 )
						 (last-insert-id dbh)))))

  )

(define (do-companies-leads)
  (let ((dbh
		 (apply simplesql-open "mysql"
				(read-conf "/mnt/kens/ki/proj/coop/sql/db-input.conf"))))
	(get-old dbh table-mapping "companies" "company_id" )	
    (simplesql-close *dbh*)))


;; EOF