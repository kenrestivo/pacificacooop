;;; $Id$
;; rename the fields to use their looong names
;; (load "/mnt/kens/ki/proj/coop/scripts/fixschema.scm")

(use-modules (ice-9 slib)
			 (database simplesql))
(require 'printf)

(load "/mnt/kens/ki/is/scheme/lib/kenlib.scm")

;; XXX note, this is fakery. you'll need to manually put in the root pw's
;;(define dbh (simplesql-open 'mysql "coop" "127.0.0.1" "paccoop" "test" "2299"))
(define dbh (simplesql-open 'mysql "coop_fake" "bc" "input" "test"))

(define new-schema '()) ;; well, here it is.
(define current-table "") ;; there has to be a more schemey way 

(define debug #t)

;; silly little diagnostic
(define (doit query)
  (if debug
	  (pp query)
	  (simplesql-query dbh query) ))

;;;;;;;; definition-processing stuff

(define (add-primary-key table line)
  (add-sub-alist new-schema table "primary key"
				 (unparen (caddr line))) )

;; if it is a COLUMN, i'll want to do:
(define (add-column table line)
  ;; NOTE! must combine before yanking ,'s!
  (set! new-schema (add-sub-alist new-schema table 
								  (car line)
								  (regexp-substitute/global #f  ",$"
													(join-strings (cdr line))
													'pre 'post)) ))
;; clean sql definition line
(define (clean-line line)
  (map (lambda (y)
		 (regexp-substitute/global #f  "[ \t]" y  'pre 'post)) 
	   (delete "" line)))

(define (unparen string)
  (regexp-substitute/global #f  "[\\(\\)]" string 'pre 'post))
  

(define (process-def line)
  (cond ((and (equal? (car line) "create")
			 (equal? (cadr line) "table"))
		 (set! current-table
			   (unparen (caddr line))))
		((and (equal? (car line) "primary")
			  (equal? (cadr line) "key"))
		 (add-primary-key current-table line))
		(else (add-column current-table line))))
 

;; make sure it is a valid line
(define (valid-def-line l)
	  (if (and 
		   (> (length l) 1)
	  (not (equal? (car l) "--"))) #t #f))

;; for loading the proper schema file
(define (load-definition deffile)
  (set! new-schema '())
  (let ((p (open-input-file deffile) ) )
	
	(do ((line (read-line p) (read-line p)))
		((or (eof-object? line) ))
	  ((lambda (x) (if (valid-def-line x)
					   (process-def x)))
	   (clean-line (string-split line #\space))))
	(close p) ))	

;;;;;;;;;;;;; pcns_schema-processing stuff

;;; unused: the pcns_schema file *should* handle all these keys
(define (fix-keys table key)
  (for-each
   (lambda (x)
	 (let ((found (assoc key (cdr x)))) ;; go get it first
	   (if (and found
				(not (equal? (car x) table)))
		   (sprintf #f "duplication of rename-column %s" key)
	   )
	 new-schema))) )


(define (rename-column items)
  (let* ((sp (string-split (car items) #\.))
		(new (string-split (cadr items) #\.))
		(long-def (assoc-ref (assoc-ref new-schema (car sp)) (cadr sp)))
		)
	(if long-def
		(doit (sprintf #f "alter table %s change column %s %s %s"
					   (car sp) (cadr sp) (cadr new) long-def))
		(printf "ignoring %s:%s\n" (car sp) (cadr sp))
		)
	)) 

;;; the easy one: tables.
(define (rename-table items)
   (doit (sprintf #f "rename table %s to %s"
		   (car items) (cadr items))))

;; simple dispatcher, using cute scheme-ism
;; in the file, tables don't have .'s in them, columns do.
(define (process-change items)
  ((if (string-index (car items) #\.)
	  rename-column
	  rename-table )
   items))


;;; this is basically MAIN, though the load-definition must occur first
(define (fix-schema fixfile)
  (let ((p (open-input-file fixfile) ) )
	(begin 
	  (do ((line (read-line p) (read-line p)))
		  ((or (eof-object? line) ))
		((lambda (x) (if (and (= 2 (length x))
							  (not (eq? (string-index (car x) #\#) 0) ))
						 (process-change x))
				 )
		 (string-split line #\space)))
	  (close p) )))

;;;;;;;;;;;;;;;
;; main

(load-definition "/mnt/kens/ki/proj/coop/sql/definition.sql")
(fix-schema "/mnt/kens/ki/proj/coop/sql/pcns_schema.txt")

(simplesql-close dbh)

;; EOF
