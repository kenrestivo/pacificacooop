;; $Id$
;; import the rasta. this is a re-do of the old perl code i wrote a year ago
;; (load "/mnt/kens/ki/proj/coop/scripts/rastaimport.scm")

(use-modules (kenlib) (srfi srfi-1)
			 (srfi srfi-13)
			 (ice-9 slib))
(require 'printf)

(define *rasta* (make-hash-table 3))

;; be sure to modify this if the damn thing ever changes
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

;;(map (lambda (x) (string-trim x)) (parse-csv line))

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

(grab-csv-rasta "AM" "/mnt/kens/ki/proj/coop/imports/AM.csv")
(grab-csv-rasta "PM" "/mnt/kens/ki/proj/coop/imports/PM.csv")
(merge-am-pm *rasta*)

;; EOF
