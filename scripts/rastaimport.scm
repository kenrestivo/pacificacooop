;; $Id$
;; import the rasta. this is a re-do of the old perl code i wrote a year ago
;; (load "/mnt/kens/ki/proj/coop/scripts/rastaimport.scm")

(use-modules (kenlib) (srfi srfi-1)
			 (ice-9 slib))
(require 'printf)

(define *rasta* (make-hash-table 3))

(define *header* '("Last Name"
				 "Mom Name *"
				 "Dad/Partner *"
				 "Child "
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

(define (merge-am-pm rasta)
  (hash-set! rasta "BOTH"
			 (append 
			  (map  (lambda (x) (append x '("AM"))) (hash-ref *rasta* "AM"))
			  (map  (lambda (x) (append x '("PM"))) (hash-ref *rasta* "PM")))))
  
(define (process-rasta-line rasta header session line )
  (let ((parsed-line (parse-csv line)))
	(if (> (length header) (length parsed-line))
		(throw 'too-short))
	(hash-set! rasta session
			   (append
				(hash-ref rasta session '())
				(list
				 (safe-list-head parsed-line (length header)))))))

(define (clean-up-rasta rasta header session)
  (hash-set! rasta session
			 (between  header (make-list (length header) #f)
					   (hash-ref rasta session))))
  
(define (grab-csv-rasta session fixfile)
  (let ((p (open-input-file fixfile) ) )
	(begin
	  (hash-set! *rasta* session '())			; well, i have to
	  (do ((line (read-line p) (read-line p)))
		  ((or (eof-object? line) ))
			(process-rasta-line *rasta* *header* session line))
	  (clean-up-rasta *rasta* *header* session)
	  (close p) )))

(grab-csv-rasta "AM" "/mnt/kens/ki/proj/coop/imports/AM.csv")
(grab-csv-rasta "PM" "/mnt/kens/ki/proj/coop/imports/PM.csv")
(merge-am-pm *rasta*)

;; EOF
