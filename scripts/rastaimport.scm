;; $Id$
;; import the rasta. this is a re-do of the old perl code i wrote a year ago
;; (load "/mnt/kens/ki/proj/coop/scripts/rastaimport.scm")

(use-modules (kenlib) (srfi srfi-1)
			 (srfi srfi-13)
			 (srfi srfi-14)
			 (database simplesql)
			 (ice-9 slib))
(require 'printf)

(define *rasta* (make-hash-table 3))

(define *debug-flag* #t)

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
;; easy version:  (string-join (cdr (string-tokenize long-parent)))
(define (split-first-last long-parent)
  (let* ((name-list (string-tokenize
					 (string-delete long-parent (char-set #\*)))
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
(define (check-for-new-parent line header column-to-check)
  (let* ((long-parent (rasta-find column-to-check line header))
		 (split-name (split-first-last long-parent))
		 (type (if (equal? column-to-check "Mom Name *") "Mom" "Dad"))
		 (worker (if (string-index long-parent (char-set #\*)) "Yes" "No"))
		 (parents (simplesql-query *dbh*
						(sprintf #f "
				select parentsid, last, first, worker, ptype familyid
						from parents left join families using (familyid)
					where (soundex(first) = soundex('%s')
						or first like \"%%%s%%\")
						and (soundex(last) = soundex('%s')
								or families.phone like \"%s\") "
								 (car split-name)
								 (car split-name)
								 (cdr split-name)
								 (rasta-find "Phone" line header)
								 ))))

		  (if (>  (length parents) 1)
			  (db-ref-last (choose-duplicates parents) "parentsid") ; got it!
			  (safe-sql *dbh* (sprintf #f "
				insert into parents set 
							last = '%s' ,
							first = '%s' ,
							worker = '%s',
							ptype = '%s',
							familyid = %d "
									   (cdr split-name)
									   (car split-name)
									   worker type
									   (check-for-new-family line header))))))



;; TODO: write a function to *update* enrollment, i.e. when people switch from am/pm
;; if needed! hopefully i'll have the web interface working before anyone changes sessions

;; the session stuff. find this first, then the family stuff.
(define (check-for-new-enrollment line header)
  (let* ((kid-id (check-for-new-kid line header))
		 (enrollments (simplesql-query *dbh*
						   (sprintf #f "
				select enrollment_id, kidsid, am_pm_session,
						start_date, dropout_date
					from enrollment
						where kidsid = %d and
						school_year = '%s'"
									kid-id
									*school-year*))))
	(if (> (length enrollments) 1)
		(db-ref-last (choose-duplicates enrollments) "enrollment_id") ;gotcha!
		(safe-sql *dbh* (sprintf #f "
						insert into enrollment set 
								kidsid = %d,
								start_date = '%s',
								school_year = '%s',
								am_pm_session = '%s'"
								 kid-id
								 *start-date*
								 *school-year* 
								 (rasta-find "session" line header)
								 )))))


(define (db-updates line header)
  (check-for-new-kid line header)
  (check-for-new-enrollment line header)
  (map (lambda (col)
		 (if (rasta-find col line header) ; handle single parents
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
  (let ((families (simplesql-query *dbh*
						   (sprintf #f "
				select familyid, name, phone from families
						where soundex(name) = soundex('%s')
						or phone like \"%%%s%%\""
									(rasta-find "Last Name" line header)
									(rasta-find "Phone" line header)))))
	(if (> (length families) 1)
		(db-ref-last (choose-duplicates families) "familyid") ;gotcha!
		(safe-sql *dbh* (sprintf #f "
						insert into families set 
								name = '%s',
								address = '%s',
								email = '%s',
								phone = '%s'"
								 (rasta-find "Last Name" line header)
								 (rasta-find "Address" line header)
								 (rasta-find "Email" line header)
								 (rasta-find "Phone" line header)
								 )))))

;; find kid
(define (check-for-new-kid line header)
  (let ((kids (simplesql-query *dbh*
						(sprintf #f "
				select kidsid, last, first, familyid from kids
					where (soundex(first) = soundex('%s') or
						first like \"%%%s%%\")
						and soundex(last) = soundex('%s') "
								 (rasta-find "Child" line header)
								 (rasta-find "Child" line header)
								 (rasta-find "Last Name" line header)
								 ))))

		  (if (>  (length kids) 1)
			  (db-ref-last (choose-duplicates kids) "kidsid") ; got it!
			  (safe-sql *dbh* (sprintf #f "
				insert into kids set 
							last = '%s' ,
							first = '%s' ,
							date_of_birth = '%s',
							familyid = %d "
									   (rasta-find "Last Name" line header)
									   (rasta-find "Child" line header)
									   (human-to-sql-date
										(rasta-find "DOB" line header))
									   (check-for-new-family line header))))))

;;;;;;;;;; functions for navigating through the rasta structure (accessors?)
(define (rasta-find key line header)
  (list-ref  line (list-position key header)))

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
