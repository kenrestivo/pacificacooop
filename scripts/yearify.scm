;;; $Id$
;; add schoolyear to columns
;; (load "/mnt/kens/ki/proj/coop/scripts/yearify.scm")

(use-modules (kenlib) (ice-9 slib)
			 (database simplesql))
(require 'printf)

(define dbh (apply simplesql-open "mysql"
				   (read-conf "/mnt/kens/ki/proj/coop/sql/db.conf")))

(define debug-flag #t)

;; silly little diagnostic
(define (doit query)
  (if debug-flag
	  (pp query)
	  (catch #t
			 (lambda ()
			   (simplesql-query dbh query) )
			 (lambda x (printf "caught error on [%s]\n" query) (pp x)))))

(define (add-new-column table column-name definition default)
  (doit (sprintf #f "alter table %s add column %s" table column-name definition))
  (doit (sprintf #f "update  %s set %s = '%s'" column-name default)))

;;;;;;;;;;;;;;;
;; main
(define tables '("auction_donation_items"
				 "packages"
				 "income"
				 "springfest_attendees"
				 "territories"))

(for-each  (lambda (table)
			 (add-new-column table "school_year" "varchar(50)" "2003-2004")
			 tables)) 

(simplesql-close dbh)


this_is_a_test "this_is another_one" now what
;; EOF
