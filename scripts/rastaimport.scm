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

(define (process-rasta-line rasta header session line )
  (hash-set! rasta session
			 (append
			  (hash-ref rasta session '())
			  (list
			   (safe-list-head (parse-csv line)
							   (length header))))))

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

;; EOF
