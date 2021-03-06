;; $Id$
;; import the rasta. this is a re-do of the old perl code i wrote a year ago
;; (load "/mnt/kens/ki/proj/coop/scripts/rastaimport.scm")

(use-modules (kenlib) (srfi srfi-1)
			 (srfi srfi-13)
			 (srfi srfi-14)
			 (database simplesql)
			 (ice-9 slib))
(require 'printf)

 (write-line "XXX fix the worker thing AND the email!, they don't work!")
 generate error here

(define *rasta* (make-hash-table 3))

(define *school-year* "2004-2005")		; should prolly calculate this too

(define *start-date* "2004-09-13")  ; this'll be a function, today, or now()

;; be sure to modify this if the damn thing ever changes
(define *header* '("Last Name"
				 "Mom Name *"
				 "Dad/Partner *"
				 "Child"
				 "DOB"
				 "Address"
				 "Phone"
				 "Email"
				 "M"
				 "Tu"
				 "W"
				 "Th"
				 "F"
				 "School Job"))

;;;;;;;;;;;;;;; functions for updating the database

(define (split-first-last long-parent)
  (let* ((name-list (string-tokenize
					 (string-delete long-parent (char-set #\*))))
		 (middle-index (list-index
					   (lambda (x) (or (equal? x "Ann")
									   (equal? x "Jo")))
					   name-list)))
	(if (false-if-exception (< 0 middle-index))
		(cons (string-join (safe-list-head name-list (+ 1 middle-index)))
			  (string-join (list-tail name-list  (+ 1 middle-index))))
		(cons (car name-list) (string-join (cdr name-list))))))

;; check for each parent column
;; TODO check for last-name changes, and fix them
;; this is an utter and complete clusterfuck. even lisp can't help me
(define (check-for-new-parent line header column-to-check)
  (let* ((long-parent (rasta-find column-to-check line header))
		 (split-name (split-first-last long-parent))
		 (type (if (equal? column-to-check "Mom Name *") "Mom" "Dad"))
		 (worker (if (string-index long-parent (char-set #\*)) "Yes" "No"))
		 (parents (simplesql-query *dbh*
								   (sprintf #f "
				select parent_id, last_name, first_name, worker, type family_id
						from parents
					where (soundex(first_name) = soundex('%s')
									or first_name like \"%%%s%%\"
									or type = '%s')
								and  family_id = %d "
											(car split-name)
											(car split-name)
											type
											(check-for-new-family line header)
										; to check index of phone
											))))

	(if (>  (length parents) 1)
		(db-ref-last (choose-duplicates parents) "parent_id") ; got it!
		(begin (safe-sql *dbh* (sprintf #f "
				insert into parents set 
							last_name = '%s' ,
							first_name = '%s' ,
							worker = '%s',
							type = '%s',
							family_id = %d "
										(cdr split-name)
										(car split-name)
										worker type
										(check-for-new-family line header)))
			   (last-insert-id *dbh*)))))



;; TODO: write a function to *update* enrollment, i.e. when people switch from am/pm
;; if needed! hopefully i'll have the web interface working before anyone changes sessions

;; the session stuff. find this first, then the family stuff.
(define (check-for-new-enrollment line header)
  (let* ((kid-id (check-for-new-kid line header))
		 (enrollments (simplesql-query *dbh*
									   (sprintf #f "
				select enrollment_id, kid_id, am_pm_session,
						start_date, dropout_date
					from enrollment
						where kid_id = %d and
						school_year = '%s'"
												kid-id
												*school-year*))))
	(if (> (length enrollments) 1)
		(db-ref-last (choose-duplicates enrollments) "enrollment_id") ;gotcha!
		(begin (safe-sql *dbh* (sprintf #f "
						insert into enrollment set 
								kid_id = %d,
								start_date = '%s',
								school_year = '%s',
								am_pm_session = '%s'"
										kid-id
										*start-date*
										*school-year* 
										(rasta-find "session" line header)
										))
			   (last-insert-id *dbh*)))))


;; the engine, which actually goes through and makes the changes
(define (db-updates line header)
  (check-for-new-kid line header)
  (check-for-new-enrollment line header)
  (map (lambda (col)
		 (if (not (equal? "" (rasta-find col line header)))
										; handle single parents
			 (check-for-new-parent line header col)))
	   '("Mom Name *" "Dad/Partner *")))

;; TODO: prompt user instead! pick amongst them
(define (choose-duplicates db-res)
  (if (> 2 (false-if-exception
			(length db-res)))
	  (error "duplicate " db-res)
	  db-res))

;; find family
(define (check-for-new-family line header)
  (let ((families (begin
					(catch #t (lambda () (simplesql-query *dbh*
						 "create temporary table tempparents (
						first_name varchar(255), last_name varchar (255),
						family_id int(32))"))
						   (lambda x #f))
					(simplesql-query *dbh*
						 "insert into tempparents select first_name, last_name,
								family_id from parents where type = 'Mom' ")
					(simplesql-query *dbh*
									 (sprintf #f "
				select families.family_id, families.name,
								families.phone, tempparents.first_name
						from families left join tempparents using (family_id)
						where  phone like \"%%%s%%\"
							or (soundex(name) = soundex('%s')
								and soundex(first_name) = soundex('%s'))"
											  (rasta-find "Phone" line header)
											  (rasta-find "Last Name"
														  line header)
											  (rasta-find "Mom Name *"
														  line header))))))
	(if (> (length families) 1)
		(db-ref-last (choose-duplicates families) "family_id") ;gotcha!
		(begin (safe-sql *dbh* (sprintf #f "
						insert into families set 
								name = '%s',
								address1 = '%s',
								phone = '%s'"
										(rasta-find "Last Name" line header)
										(rasta-find "Address" line header)
										(rasta-find "Phone" line header)
										))
			   (last-insert-id *dbh*)))))

;; find kid
(define (check-for-new-kid line header)
  (let ((kids (simplesql-query *dbh*
							   (sprintf #f "
				select kid_id, last_name, first_name, family_id from kids
					where (soundex(first_name) = soundex('%s') or
						first_name like \"%%%s%%\")
						and soundex(last_name) = soundex('%s') "
										(rasta-find "Child" line header)
										(rasta-find "Child" line header)
										(rasta-find "Last Name" line header)
										))))

	(if (>  (length kids) 1)
		(db-ref-last (choose-duplicates kids) "kid_id") ; got it!
		(begin (safe-sql *dbh* (sprintf #f "
				insert into kids set 
							last_name = '%s' ,
							first_name = '%s' ,
							date_of_birth = '%s',
							family_id = %d "
										(rasta-find "Last Name" line header)
										(rasta-find "Child" line header)
										(human-to-sql-date
										 (rasta-find "DOB" line header))
										(check-for-new-family line header)))
			   (last-insert-id *dbh*)))))

;;;;;;;;;; functions for navigating through the rasta structure (accessors?)
(define (rasta-find key line header)
  (let ((res (list-ref  line (list-position key header))))
	(if res res "")))

;;;;;;;;;;; functions for importing and cleaning csv's from spreadsheet

;; each session gets dumped in separately, but then gets fixed here.
(define (merge-am-pm rasta header)
  (hash-set! rasta "BOTH"
			 (append 					; when i get elite, use macro here
			  (map  (lambda (x) (append x '("AM"))) (hash-ref *rasta* "AM"))
			  (map  (lambda (x) (append x '("PM"))) (hash-ref *rasta* "PM"))))
  (append header (list "session")))

;; wrapper around between: remove the header and footer crap
(define (clean-up-rasta rasta header session)
  (hash-set! rasta session
			 (between  header
					   (make-list (length header) #f)
					   (map
						(lambda (x) (safe-string-trim x))
						(hash-ref rasta session)))))

;; each line.
(define (process-rasta-line rasta header session line )
  (letrec ((parsed-line
			(map (lambda (x) (safe-string-trim x)) (parse-csv line))))
	(if (> (length header) (length parsed-line))
		(throw 'too-short))				;  excel sucks, as does parse-csv
	(hash-set! rasta session
			   (append
				(hash-ref rasta session '())
				(list
				 (safe-list-head parsed-line (length header)))))))


;; iterate through file, happily singing along the way
(define (grab-csv-rasta session fixfile)
  (let ((p (open-input-file fixfile) ) )
	(begin
	  (hash-set! *rasta* session '())			; well, i have to
	  (do ((line (read-line p) (read-line p)))
		  ((or (eof-object? line) ))
			(process-rasta-line *rasta* *header* session line))
	  (clean-up-rasta *rasta* *header* session)
	  (close p) )))

;;;;;;;;;;;; main

(define *dbh* (apply simplesql-open "mysql"
				   (read-conf "/mnt/kens/ki/proj/coop/sql/db-input.conf")))

(grab-csv-rasta "AM" "/mnt/kens/ki/proj/coop/imports/AM.csv")
(grab-csv-rasta "PM" "/mnt/kens/ki/proj/coop/imports/PM.csv")
(set! *header* (merge-am-pm *rasta* *header*))

;; ok, start the pachinko machine!
(for-each (lambda(line) (db-updates line *header*))
		  (hash-ref *rasta* "BOTH"))

(simplesql-close *dbh*)

;; EOF
