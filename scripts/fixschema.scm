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

;; clean sql definition line
;; TODO! must combine before yanking ,'s!
(define (clean-line line)
		 (map (lambda (y)
				 (regexp-substitute/global #f  "[ \t]" y  'pre 'post)) 
		  (map (lambda (x)
				 (regexp-substitute/global #f  ",$" x  'pre 'post)) 
			   (delete "" line))))

;; if it is a COLUMN, i'll want to do:
;;(cons (car tl) (string-join (cdr tl)))

;; make sure it is a valid line
(define (valid-def-line l)
	  (if (and 
		   (> (length l) 1)
	  (not (equal? (car l) "--"))) #t #f))

;; for loading the proper schema file
(define (load-definition deffile)
  (let ((p (open-input-file deffile) ) )
	
	(do ((line (read-line p) (read-line p)))
		((or (eof-object? line) ))
	  ((lambda (x) (if (valid-def-line x)
					   (pp x)))
	   (clean-line (string-split line #\space))))
	 (close p) ))	

;; EOF
