;; $Id$
;; import the rasta. this is a re-do of the old perl code i wrote a year ago


(define *header-found* #f)
(define *empty-line* #f)

(define (check-header line)
  (let ((header '("Last Name"
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
				  "School Job")))

)
  
(define (process-rasta-line session line)
  (set! *header-found* (or *header-found* (check-header line)))
  (set! *empty-line* (check-empty line))
  (if (and *header-found*  (not *empty-line))
	  (list->vector (cons session (parse-csv line)))))


(define (grab-csv-rasta session fixfile)
  (let ((p (open-input-file fixfile) ) )
	(begin
	  (set! *header-found* #f)
	  (set! *empty-line* #f)
	  (do ((line (read-line p) (read-line p)))
		  ((or (eof-object? line) ))
			(process-rasta-line session line))
	  (if (not *header-found*) (write-line "no header found")) ; TODO session
	  (close p) )))

(grab-csv-rasta "am" "/mnt/kens/ki/proj/coop/imports/AM.csv")
(grab-csv-rasta "pm" "/mnt/kens/ki/proj/coop/imports/PM.csv")

;; EOF