;;; $Id$
;; merge the old audit stuff out, into the new paradigm for the modern world.
;; (load "/mnt/kens/ki/proj/coop/scripts/fixaudits.scm")

(use-modules (ice-9 slib)
			 (database simplesql))
(require 'printf)



(define fixems '(
  ("leads" "lead_id") 
  ("faglue" "auction_items_families_join_id") 
  ("families" "family_id")
  ("figlue" "families_income_join_id")
  ("groupmembers" "user_id")
  ("inc" "income_id")
  ("kids" "kid_id")
  ("lic" "drivers_license_id")
  ("nags" "nag_id")
  ("parents" "parent_id")
  ("privs" "privilege_id")
  ("users" "user_id")
  ("veh" "vid_number") ))

(define (sql-length tabinfo) 
	(> (length 
		(safe-sql *dbh*  (sprintf #f 
					   "select * from %s where audit_user_id is not null"
					   (car tabinfo)))) 
	   1 ))

	
(define (move-audits tabinfo)
	(for-each (lambda (col)
				(safe-sql *dbh* (sprintf #f 
					"replace into audit_trail 
							(table_name, index_id, audit_user_id, updated) 
					  select '%s', %s, audit_user_id, %s 
						from %s where %s is not null
							and audit_user_id is not null"
					  (car tabinfo) (cadr tabinfo) col 
					  (car tabinfo) col)))
			  '("entered" "updated")))
	
(define (toast-columns tabinfo)
	(for-each (lambda (col)
				(safe-sql *dbh*  (sprintf #f 
									   "alter table %s drop column %s"
									   (car tabinfo) col ))) 
			  '("entered" "updated" "audit_user_id")))

;; main

(define *dbh* (apply simplesql-open "mysql"
				   (read-conf "/mnt/kens/ki/proj/coop/sql/db-ken.conf")))

(for-each (lambda (x) 
			(if (sql-length x) 
				(begin 
				  (move-audits x) 
				  (toast-columns x)))
		  fixems))

(simplesql-close *dbh*)

;;; EOF
