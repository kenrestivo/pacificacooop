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
  ("nag_indulgences" "nag_indulgence_id")
  ("nags" "nagsid")
  ("parents" "parentsid")
  ("privs" "privid")
  ("users" "userid")
  ("veh" "vidnum") ))

(define sql-length 
  (lambda (tabinfo) 
	(> (vector-length 
		(sql-query 0 
	 			   (sprintf #f "select * from %s where audit_user_id is not null"
							(car tabinfo)))) 1 )))

			;;"replace into audit_trail 
				;;		(table_name, index_id, audit_user_id, updated) 
	
(define move-audits
  (lambda (tabinfo)
		(sql-query 0 
				   (sprintf #f 
					"select '%s', %s, audit_user_id, %s 
						from %s where audit_user_id is not null"
					(car tabinfo) (cadr tabinfo) "entered" (car tabinfo)))))
	
(for-each (lambda (x) (if (sql-length x) (move-audits x))) fixems)

;;; EOF
