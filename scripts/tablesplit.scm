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
  '("parents"
	"invitations"
	"company_contacts"
	"insurance_information"
	"drivers_licenses"))

(define sites-tables
  '("invitations"
	"companies"
	"families"))

(define org-tables
  '("invitations"
	"companies"
	"flyer_locations"
	"raffle_locations"))

(define people-fields
  '("first_name"
	"last_name"
	"title" 
	"salutation" 
	"email_address"))

(define sites-fields
  '("address1"
  "address2"
  "city"
  "state"
  "zip"
  "country"
  "phone"
  "fax"))

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

;; EOF