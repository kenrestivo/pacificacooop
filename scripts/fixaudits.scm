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
	

(for-each (lambda (x) (sql-length x)) fixems)

;;; EOF
