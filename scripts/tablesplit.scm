;; $Id$
;; move from everything-globbed-together to split tables
;; required for new solicitation/invitations stuff
;; (load "/mnt/kens/ki/proj/coop/scripts/tablesplit.scm")


(use-modules (kenlib)
			 (srfi srfi-1)
			 (srfi srfi-13)
			 (srfi srfi-14)
			 (database simplesql)
			 (ice-9 slib))
(require 'printf)

(define  people-tables
  '(("parents" . "parent_id")
	("leads" . "leads_id")
	("company_contacts" . "company_contact_id")
	("insurance_information" . "insurance_information_id")
	("drivers_licenses" . "drivers_license_id"))

(define sites-tables
  '(("leads" . "lead_id")
	("companies"  . "company_id")
	("families" . "family_id"))

(define org-tables
  '(("leads" . "lead_id")
	("companies" . "company_id")
	("flyer_locations" . "flyer_location_id")
	("raffle_locations" . "raffle_location_id"))

(define people-fields
  '("first_name"
	"last_name"
	"title" 
	"salutation" 
	"email_address"))
(define people-keys
  '("first_name" "last_name"))

(define sites-fields
  '("address1"
  "address2"
  "city"
  "state"
  "zip"
  "country"
  "phone"
  "fax"))

(define sites-keys
  '("address1" "city"))

(define org-fields
  '("company_name"
	"URL"
	"flyer_ok"))

;;;;;;;; ok, funcs

(define (get-cols dbh tablename)
  (slice-results dbh 0 (string-append "explain " tablename)))

(define (get-fields dbh table-name field-list)
  (filter (lambda (x) (member x field-list)) (get-cols dbh table-name)))

(define (get-everything dbh index-column table-name field-list)
  (simplesql-query dbh
				   (sprintf #f "select %s, %s from %s"
							index-column
							(string-join
							 (get-fields dbh table-name field-list) ", ")
							table-name)))

(define (choose-duplicates db-res)
  (if (> 2 (false-if-exception
			(length db-res)))
	  (error "duplicate " db-res)
	  db-res))

;; return a cons of the fieldname and its data
(define (make-indexed-result index-col field-list result-vector)
  (map (lambda (x y) (cons x y ))
	   (append (list index-col) field-list)
	   (un-null result-vector)))


;; takes in a list of indexed fields
(define (check-for-new-people dbh key-indexed-fields all-indexed-fields
							  new-table )
  (let* ((search-results
		  (simplesql-query dbh
						   (sprintf #f "select %s , %s from %s where %s "
									to-index
									(string-join
									 (map (lambda (x)
											(car x))
										  key-indexed-fields)
									 ", ")
									new-table
									(make-like-line  key-indexed-fields)
									))))
	(if (>  (length search-results) 1)
		(db-ref-last (choose-duplicates search-results)
					 (car (car all-indexed-fields))) ; got it!
		(begin (safe-sql dbh (sprintf #f "
				insert into %s set % "
									  new-table
									  (make-set-line (cdr))
									  
									  
									  ))
			   (last-insert-id dbh)))))

;; EOF