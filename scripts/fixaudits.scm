;;; $Id$
;; merge the old audit stuff out, into the new paradigm for the modern world.
;; (load "/mnt/kens/ki/proj/coop/scripts/fixaudits.scm")

(use-modules (ice-9 slib))
(require 'printf)
(require 'pretty-print)

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
	
(for-each (lambda (x) 
			(if (sql-length x) 
				(move-audits x))) 
		  fixems)

;;; EOF
