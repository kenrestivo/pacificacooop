;;; $Id$
;; use the proper family name in leads where all i have is "family"
;; grab the family name from the income stuff.
;; (load "/mnt/kens/ki/proj/coop/scripts/fixfamilies.scm")

(use-modules (ice-9 slib)
			 (database simplesql))
(require 'printf)

;;(load "/mnt/kens/ki/is/scheme/lib/kenlib.scm")

;; XXX note, this is fakery. you'll need to manually put in the root pw's
;;(define dbh (simplesql-open 'mysql "coop" "127.0.0.1" "input" "test" "2299"))
(define dbh (simplesql-open 'mysql "coop" "bc" "input" "test"))
	
(for-each 
 (lambda (vec)
   (simplesql-query dbh
					(sprintf #f "update leads set last = '%s'
							where leadsid = %d" 
							 (vector-ref vec 2)
							 (vector-ref vec 0))))
 (cdr (simplesql-query dbh
			 "select leads.leadsid, leads.last, inc.payer 
				from leads left join invitation_rsvps 
					on leads.leadsid = invitation_rsvps.leadsid 
				left join inc on invitation_rsvps.incid = inc.incid 
			where last like \"%Family%\" 
				and invitation_rsvps.incid is not null")))


(simplesql-close dbh)

;;; EOF
