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


(define (change-privs dbh user-name realm group-level user-level)
  (let* ((user-id (get-user-id dbh user-name))
		 (lov (simplesql-query dbh (sprintf #f "
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
		  (last-insert-id dbh)))))

(define (get-user-id dbh name)
  (last-item (simplesql-query dbh (sprintf #f "
				select user_id from users where name like \"%%%s%%\" "
								name))))
(define (change-privs-list dbh list-of-names realm group-level user-level)
  (for-each (lambda (user-name)
			  (begin
				(change-privs dbh user-name realm group-level user-level)))
            list-of-names))


;;;;; the various committee defaults here
(define (springfest-goddesses dbh list-of-names)
  (for-each (lambda (user-name)
			  (for-each (lambda (realm)
						  (change-privs dbh user-name realm 800 800))
						springfest-realms))
			list-of-names))

(define (solicits dbh list-of-names)
  (change-privs-list dbh list-of-names "solicitation" 200 700)
  (change-privs-list dbh list-of-names "solicit_money" 100 200))


(define (do-chairs dbh chairs)
  (for-each
   (lambda (chair-pair)
	 (change-privs dbh (cdr chair-pair) (car chair-pair) 800 800))
   chairs))


;;;;;;;;;;;
;; now do stuff
(define chairs
  '(
	("solicitation" . "bauer")
	("packaging" . "White-Roger+Michelle")
    ("invitations" . "david")
    ("raffle" . "ambrose")
    ("tickets" . "stangeland")
    ("money" . "devry")
		))



(define (update-2004-2005)
  (let ((dbh (apply simplesql-open "mysql"
					(read-conf "/mnt/kens/ki/proj/coop/sql/db-kens.conf"))))
	;; now the solicitation
	 (solicits dbh
			   '("depriest" "refino" "kaitz" "solano" "mrad" "gaffney" "bauer"))
     ;; packaging committee
     (change-privs-list dbh '("white-roger+michelle" "walker" "pacheco")
                        "packaging" 700 500)
     ;; program committee: packages
     (change-privs-list dbh '("treckeme" "stewart")
                        "packaging" 200 200 )
     ;; program: so they can see the ads too
     (change-privs-list dbh '("treckeme" "stewart")
                        "solicitation" 200 200 )
     ;; publicity committee
     (change-privs-list dbh '("manning-villlar" "cunniffe" "glasman"
                              "klieder" "simonson")
                        "flyers" 200 700)
     ;; raffle committee
     (change-privs-list dbh '("ambrose" "cresci-torres" )
                        "raffle" 200 200)
     ;; ticket committee
     (change-privs-list dbh '("fitzpatrick" "stangeland")
                        "tickets" 200 200)
     ;; invitations committee
     (change-privs-list dbh '("david" "devry") "invitations" 700 600)
     
     ;; nag committee
     (change-privs-list dbh '("julian" "blackman") "nag" 200 200)

     ;; the chairs, overriding defaults
	 (do-chairs dbh chairs)
     
	 ;; finally the admins, overriding al;
	 (springfest-goddesses dbh '("vreeland" "cooke"))

     (simplesql-close dbh)))




;; EOF