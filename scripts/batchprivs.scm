;;; $Id$
;; add schoolyear to columns
;; (load "/mnt/kens/ki/proj/coop/scripts/batchprivs.scm")

(use-modules (kenlib) (ice-9 slib)
			 (database simplesql))
(require 'printf)


;;;;;;;;;;; globs


(define springfest-realms
  '("auction"
  "flyers"
  "invitations"
  "invitations_cash"
  "money"
  "nag"
  "packaging"
  "raffle"
  "roster"
  "solicitation"
  "solicit_money"
  "tickets"))

;;;;;;;;;;;;; funcs

(define (get-all-realms dbh)
  (map (lambda (x) (vector-ref x 0))
	   (simplesql-query dbh "
		select realm from user_privileges group by realm order by realm")))


(define (change-privs dbh user-id realm group-level user-level)
  (let* ((lov (simplesql-query dbh (sprintf #f "
				select * from user_privileges where user_id = %d 
						and realm = '%s'" user-id realm)))
		 (id (db-ref-last lov "privilege_id")))
	(if (> (length lov) 1)
		(begin
		  (safe-sql dbh (sprintf #f "
				update user_privileges set group_level = %d, user_level = %d
					 where privilege_id = %d"
								   group-level user-level id))
		  id)
		(begin
		  (safe-sql dbh (sprintf #f "
				insert into user_privileges set user_id = %d, 
						realm = '%s', group_level = %d, user_level = %d"
								   user-id realm group-level user-level))
		  (last-insert-id *dbh*)))))

(define (get-user-id dbh name)
  (last-item (simplesql-query dbh (sprintf #f "
				select user_id from users where name like \"%%%s%%\" "
								name))))

;;; ok, now do it!
(define (springfest-gods)
  (let ((dbh (apply simplesql-open "mysql"
					(read-conf "/mnt/kens/ki/proj/coop/sql/db-input.conf"))))
	(for-each (lambda (user-id)
				(for-each (lambda (realm)
							(change-privs dbh user-id realm 800 800))
						  springfest-realms))
			  (map (lambda (x) (get-user-id dbh x))
				   '("vreeland" "cooke")))
	(simplesql-close dbh)))





;; EOF