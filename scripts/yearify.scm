




;;; $Id$
;; add schoolyear to columns
;; (load "/mnt/kens/ki/proj/coop/scripts/yearify.scm")

(use-modules (kenlib) (ice-9 slib)
			 (database simplesql))
(require 'printf)

(define dbh (apply simplesql-open "mysql"
				   (read-conf "/mnt/kens/ki/proj/coop/sql/db.conf")))


(define *debug-flag* #t)

;;;;;;;;;;;;;;;
;; main

(for-each  (lambda (table)
			 (add-new-column dbh table
							 "school_year" "varchar(50)" "2003-2004"))
		   '("auction_donation_items"
			 "packages"
			 "income"
			 "springfest_attendees"
			 "territories"))


(simplesql-close dbh)


;; EOF
