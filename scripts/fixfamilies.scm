;;; $Id$
;; use the proper family name in leads where all i have is "family"
;; grab the family name from the income stuff.
;; (load "/mnt/kens/ki/proj/coop/scripts/fixfamilies.scm")

(use-modules (ice-9 slib)
			 (kenlib)
			 (database simplesql))
(require 'printf)


(define *debug-flag* #t)

(define *dbh* (apply simplesql-open "mysql"
				   (read-conf "/mnt/kens/ki/proj/coop/sql/db-input.conf")))

	
(for-each 
 (lambda (vec)
   (safe-sql *dbh*
					(sprintf #f "update leads set last = '%s'
							where leadsid = %d" 
							 (vector-ref vec 2)
							 (vector-ref vec 0))))

 (cdr (simplesql-query *dbh*
			 "select leads.leadsid, leads.last, inc.payer 
				from leads left join invitation_rsvps 
					on leads.leadsid = invitation_rsvps.leadsid 
				left join inc on invitation_rsvps.incid = inc.incid 
			where last like \"%Family%\" 
				and invitation_rsvps.incid is not null")))


(simplesql-close *dbh*)

;;; EOF
