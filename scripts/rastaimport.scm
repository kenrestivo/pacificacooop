;; $Id$
;; import the rasta. this is a re-do of the old perl code i wrote a year ago

(use-modules (kenlib) (srfi srfi-1)
			 (ice-9 slib))
(require 'printf)

(define *rasta* '())

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

(define (process-rasta-line header session line )
  (set! *rasta* (append *rasta* (list
								   (list-head (parse-csv line)
										 (length header))))))

(define (clean-up-rasta rasta header)
  (between  header (make-list (length header) #f) rasta ))
  
(define (grab-csv-rasta session fixfile)
  (let ((p (open-input-file fixfile) ) )
	(begin
	  (set! *rasta* '())
	  (do ((line (read-line p) (read-line p)))
		  ((or (eof-object? line) ))
			(process-rasta-line *header* session line))
	  (close p) )))

(grab-csv-rasta "am" "/mnt/kens/ki/proj/coop/imports/AM.csv")
(grab-csv-rasta "pm" "/mnt/kens/ki/proj/coop/imports/PM.csv")

;; EOF