;; $Id$
;; import the rasta. this is a re-do of the old perl code i wrote a year ago

(use-modules (kenlib) 
			 (ice-9 slib))
(require 'printf)

(define *rasta* '())

(define header '("Last Name"
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

(define (process-rasta-line session line)
  (set! *rasta* (append *rasta* (list
								   (list-head (parse-csv line)
										 (length header))))))
(define (clean-up-rasta rasta header)
  (cdr (member header rasta)))

(define (grab-csv-rasta session fixfile)
  (let ((p (open-input-file fixfile) ) )
	(begin
	  (set! *rasta* '())
	  (do ((line (read-line p) (read-line p)))
		  ((or (eof-object? line) ))
			(process-rasta-line session line))
	  (set! *rasta* (clean-up-rasta *rasta*))
	  (close p) )))

(grab-csv-rasta "am" "/mnt/kens/ki/proj/coop/imports/AM.csv")
(grab-csv-rasta "pm" "/mnt/kens/ki/proj/coop/imports/PM.csv")

;; EOF