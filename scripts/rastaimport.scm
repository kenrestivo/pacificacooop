;; $Id$
;; import the rasta. this is a re-do of the old perl code i wrote a year ago

(define (grab-csv-file fixfile)
  (let ((p (open-input-file fixfile) ) )
	(begin 
	  (do ((line (read-line p) (read-line p)))
		  ((or (eof-object? line) ))
		(pp (parse-csv line))
		)
	  (close p) )))

(grab-csv-file "/mnt/kens/ki/proj/coop/imports/AM.csv")

;; EOF