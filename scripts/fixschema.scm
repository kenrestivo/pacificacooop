;;; $Id$
;; rename the fields to use their looong names
;; (load "/mnt/kens/ki/proj/coop/scripts/fixschema.scm")

(use-modules (ice-9 slib)
			 (database simplesql))
(require 'printf)

;;(load "/mnt/kens/ki/is/scheme/lib/kenlib.scm")

;; XXX note, this is fakery. you'll need to manually put in the root pw's
;;(define dbh (simplesql-open 'mysql "coop" "127.0.0.1" "paccoop" "test" "2299"))


(define (rename-table-query items)
   (sprintf #f "rename table %s to %s"
		   (car items) (cadr items))) 

;; TODO i have to fish the definition out of the definition.sql,
;; or out of a mysqldump somewhere
(define (rename-column-query items)
  (let ((sp (string-split (car items) #\.))
		(new (string-split (cadr items) #\.)))
	(sprintf #f "alter table %s change column %s %s"
				 (car sp) (cadr sp) (cadr new))) )

;; simple dispatcher, using cute scheme-ism
(define (rename-query items)
  ((if (string-index (car items) #\.)
	  rename-column-query 
	  rename-table-query )
   items))

(define (fix-schema fixfile)
  (let ((p (open-input-file fixfile) ) )
	(begin 
	  (do ((line (read-line p) (read-line p)))
		  ((or (eof-object? line) ))
		((lambda (x) (if (and (= 2 (length x))
							  (not (eq? (string-index (car x) #\#) 0) ))
						 (pp (rename-query x))
						 ))
		 (string-split line #\space)))
	  (close p) )))


;;; EOF
