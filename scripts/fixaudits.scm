;;; $Id$
;; merge the old audit stuff out, into the new paradigm for the modern world.
;; (load "/mnt/kens/ki/proj/coop/scripts/fixaudits.scm")

(use-modules (ice-9 slib))
(require 'printf)

;; XXX note, this is fakery. you'll need to manually put in the root pw's
;;(sql-create "coop" "127.0.0.1" "input" "test" "2299")
(sql-create "coop" "bc" "input" "test")

(define fixems '(
  ("faglue" "faglueid") 
  ("families" "familyid")
  ("figlue" "figlueid")
  ("groupmembers" "memberid")
  ("inc" "incid")
  ("kids" "kidsid")
  ("lic" "licid")
  ("nags" "nagsid")
  ("parents" "parentsid")
  ("privs" "privid")
  ("users" "userid")
  ("veh" "vidnum") ))

(define (sql-length tabinfo) 
	(> (vector-length 
		(sql-query 0  (sprintf #f 
					   "select * from %s where audit_user_id is not null"
					   (car tabinfo)))) 
	   1 ))

	
(define (move-audits tabinfo)
	(for-each (lambda (col)
				(sql-query 0 (sprintf #f 
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
				(sql-query 0  (sprintf #f 
									   "alter table %s drop column %s"
									   (car tabinfo) col ))) 
			  '("entered" "updated" "audit_user_id")))

;; main
(for-each (lambda (x) 
			(if (sql-length x) 
				(begin 
				  (move-audits x) 
				  (toast-columns x)))
		  fixems))

(sql-destroy 0)

;;; EOF
