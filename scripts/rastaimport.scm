;; $Id$
;; import the rasta. this is a re-do of the old perl code i wrote a year ago
;; (load "/mnt/kens/ki/proj/coop/scripts/rastaimport.scm")

(use-modules (kenlib) (srfi srfi-1)
			 (srfi srfi-13)
			 (database simplesql)
			 (ice-9 slib))
(require 'printf)

(define *rasta* (make-hash-table 3))

(define *debug-flag* #t)


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
						where name like \"%%%s%%\" and phone like \"%%%s%%\""
									(rasta-find "Last Name" line header)
									(rasta-find "Phone" line header)))))
	(if (> (length families) 1)
		(db-ref-last (choose-duplicates families) "familyid") ;gotcha!
		(safe-sql *dbh* (sprintf #f "
						insert into families set 
								name = '%s',
								phone = '%s'"
								 (rasta-find "Last Name" line header)
								 (rasta-find "Phone" line header)
								 )))))

;; find kid
(define (check-for-new-kid line header)
  (let ((kids (simplesql-query *dbh*
						(sprintf #f "
				select kidsid, last, first, familyid from kids
					where first like \"%%%s%%\" and last like \"%%%s%%\" "
								 (rasta-find "Child" line header)
								 (rasta-find "Last Name" line header)
								 ))))

		  (if (>  (length kids) 1)
			  (db-ref-last (choose-duplicates kids) "kidsid") ; got it!
			  (safe-sql *dbh* (sprintf #f "
				insert into kids set 
							last = '%s' ,
							first = '%s' ,
							familyid = %d "
									   (rasta-find "Last Name" line header)
									   (rasta-find "Child" line header)
									   (check-for-new-family line header))))))

;;;;;;;;;; functions for navigating through the rasta structure (accessors?)
(define (rasta-find key line header)
  (list-ref  line (list-position key header)))

;;;;;;;;;;; functions for importing and cleaning csv's from spreadsheet

;; each session gets dumped in separately, but then gets fixed here.
(define (merge-am-pm rasta)
  (hash-set! rasta "BOTH"
			 (append 					; when i get elite, use macro here
			  (map  (lambda (x) (append x '("AM"))) (hash-ref *rasta* "AM"))
			  (map  (lambda (x) (append x '("PM"))) (hash-ref *rasta* "PM")))))

;; wrapper around between: remove the header and footer crap
(define (clean-up-rasta rasta header session)
  (hash-set! rasta session
			 (between  header (make-list (length header) #f)
					   (hash-ref rasta session))))

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
(merge-am-pm *rasta*)


(simplesql-close *dbh*)

;; EOF
