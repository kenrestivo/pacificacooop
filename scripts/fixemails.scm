;;; $Id$
;; put the parentid in for all the solicitation auction and money
;; one-off script to handle my having stuck the emails in the wrong place
;; (load "/mnt/kens/ki/proj/coop/scripts/fixemails.scm")

(use-modules (ice-9 slib)
			 (kenlib)
			 (database simplesql))
(require 'printf)


(define *dbh* (apply simplesql-open "mysql"
					 (read-conf "/mnt/kens/ki/proj/coop/sql/db-input.conf")))


(for-each 
 (lambda (record)
   (safe-sql *dbh*
			 (sprintf #f "update families set email = '%s' 
						where family_id = %d and email is null"
					  (vector-ref record 0)
					  (vector-ref record 1)
					  )))
 
 (cdr (simplesql-query *dbh*
					   "select parents.email_address, parents.family_id
				from parents
						left join families using (family_id)
				where parents.email_address is not null
						and families.email is null
				group by parents.family_id
			order by parents.family_id")))


(simplesql-close *dbh*)

;;; EOF
