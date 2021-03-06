;;; $Id$
;; add schoolyear to columns
;; (load "/mnt/kens/ki/proj/coop/scripts/yearify.scm")

(use-modules (kenlib) (ice-9 slib)
			 (database simplesql))
(require 'printf)

(define *dbh* (apply simplesql-open "mysql"
				   (read-conf "/mnt/kens/ki/proj/coop/sql/db-ken.conf")))


;;;;;;;;;;;;;;;
;; main

;; NOTE: this will *not* move any old data around.
;; it is for both the OLD and NEW database schemata
;; you need a new-architecture  script to do it
(for-each (lambda (args) (apply add-new-column *dbh* args))
		  '(("kids" "date_of_birth" "date" #f)
			("families" "address1" "varchar(255)" #f)
			("families" "email" "varchar(255)" #f)))

;; NOTE! this is for NEW column names but OLD schema.
;; i need to run this *after* running fixschema
(for-each  (lambda (table)
			 (add-new-column *dbh* table
							 "school_year" "varchar(50)" "2003-2004"))
		   '("auction_donation_items"
			 "packages"
			 "income"
			 "nags"
			 "leads"
			 "flyer_deliveries"
			 "nag_indulgences"
			 "springfest_attendees"
			 "territories"))

;; make sure i don't have any idle school_years
(for-each (lambda (table)
			(safe-sql *dbh*
					  (sprintf #f "
				update %s set school_year = \"2003-2004\" 
						where school_year is null" table)))
		  (map
		   (lambda (x) (vector-ref x 0))
		   (simplesql-query *dbh* "show tables")))


(simplesql-close *dbh*)


;; EOF