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
	("family_id" . "family_id") 
	("do_not_contact" . "do_not_contact")
	("company_id" . "company_id")))

(define old-cols
  '(
	)
  

(define (replace-new dbh cols new-table old-table)
  (safe-sql dbh
			(sprintf #f "replace into %s (%s) 
						select %s from %s"
					 new-table
					 (string-join
					  (map (lambda (x) (cdr x)) cols) ", ")
					 (string-join
					  (map (lambda (x) (car x)) cols) ", ")
					 old-table)))



(define (do-companies-leads)
  (let ((dbh
		 (apply simplesql-open "mysql"
				(read-conf "/mnt/kens/ki/proj/coop/sql/db-input.conf"))))
	(replace-new dbh table-mapping "leads" "companies"  )	
; 	(remove-old dbh old-cols)			
	(simplesql-close *dbh*)))


;; EOF