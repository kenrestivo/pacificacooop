;;; $Id$
;; rename the fields to use their looong names
;; (load "/mnt/kens/ki/proj/coop/scripts/fixschema.scm")

(use-modules (kenlib) (ice-9 slib)
			 (database simplesql))
(require 'printf)



;;;;;;;; old-definition schema-processing stuff
(define *main-schema* '()) ;; this is an aLIST of ACONS'es
(define *current-table* "") ;; there has to be a more schemey way

;; setter
(define (add-primary-key table line)
  (add-sub-alist *main-schema* table "primary key"
				 (unparen (caddr line))) )

;; accessor
(define (get-primary-key table schema)
  (assoc-ref (assoc table schema) "primary key"))

;; accessor
(define (get-definition col table schema)
  (assoc-ref (assoc table schema) col))

;; setter: if it is a COLUMN, i'll want to do:
(define (save-schema-column! table line)
  ;; NOTE! must combine before yanking ,'s!
  (set! *main-schema* (add-sub-alist *main-schema* table 
						  (car line)
						  ;; note, the line is what gets fed IN to regexp
						  (regexp-substitute/global #f  ",$"
											(join-strings (cdr line))
											'pre 'post)) ))
;; utility: clean sql definition line
(define (clean-line line)
  (map (lambda (y)
		 (regexp-substitute/global #f  "[ \t]" y  'pre 'post)) 
	   (delete "" line)))

;; utility
(define (unparen string)
  (regexp-substitute/global #f  "[\\(\\)]" string 'pre 'post))
  

;; distpatch to the proper setter
(define (process-def! line)
  (cond ((and (equal? (car line) "create")
			 (equal? (cadr line) "table"))
		 (set! *current-table*
			   (unparen (caddr line))))
		((and (equal? (car line) "primary")
			  (equal? (cadr line) "key"))
		 (add-primary-key *current-table* line))
		(else (save-schama-column! *current-table* line))))


;; utliity: discard any -- comment lines
(define (valid-def-line l)
  (if (and 
	   (> (length l) 1)
	   (not (equal? (car l) "--"))) #t #f))

;; main, kind of: for loading the proper schema file
(define (load-definition! deffile)
  (set! *main-schema* '())
  (let ((p (open-input-file deffile) ) )
	
	(do ((line (read-line p) (read-line p)))
		((or (eof-object? line) ))
	  ((lambda (x) (if (valid-def-line x)
					   (process-def! x)))
	   (clean-line (string-split line #\space))))
	(close p) ))	

;;;;;;;;;;;;; pcns_schema-processing stuff
;; TODO: add tables to the change-alist. if data isn't a pair, it's a table
(define *tables* '()) ;; hack. need to handle tables last.
(define *change-alist* '())


;; find and change any columns which use this primary key!
;; TODO replace table/old/new with that alist
(define (fix-primary-key key-table old-col new-col schema)
  (for-each
   (lambda (linked-table)
										; only if this is a primary key!
	 (if (and (equal? (get-primary-key key-table schema) old-col)
			  (assoc-ref linked-table old-col) ; it exists in this table
			  (not (equal? (car linked-table) key-table))) ; i'm not primary
		 (safe-sql *dbh* (sprintf #f "alter table %s change column %s %s %s"
						(car linked-table) old-col new-col
						; get the definition from the actual subtable
						(get-definition old-col (car linked-table) schema)))
			   ))
	 schema))

;; setter: the change-alist
(define (save-rename-column items schema)
  (let* ((sp (string-split (car items) #\.))
		 (new (string-split (cadr items) #\.))
		 (table (car sp))
		 (old-col (cadr sp))
		 (new-col (cadr new))
		 (long-def (assoc-ref (assoc-ref schema (car sp)) (cadr sp)))
		 )
	(if long-def
		(set! *change-alist*
			  (add-sub-alist *change-alist* table old-col new-col))	
		;; TODO: be smart and check first if it is already changed
		(printf "IGNORING: rename spec  %s:%s is NOT in schema, can't change to %s\n" table old-col new-col) ;; it's a bogus line? huh?
		)
	))

;; make this the func for actually MAKING changes
(define (do-something-with-this items schema)
  (let* ((sp (string-split (car items) #\.))
		 (new (string-split (cadr items) #\.))
		 (table (car sp))
		 (old-col (cadr sp))
		 (new-col (cadr new))
		 (long-def (assoc-ref (assoc-ref schema (car sp)) (cadr sp)))
		 )
	(if long-def
		(begin 
		  (safe-sql *dbh* (sprintf #f "alter table %s change column %s %s %s"
						 table old-col new-col long-def))
		  (fix-primary-key table old-col new-col schema) )
		;; TODO: be smart and check first if it is already changed
		(printf "IGNORING: rename spec  %s:%s is NOT in schema, can't change to %s\n" table old-col new-col) ;; it's a bogus line? huh?
		)
	))

;;; the easy one: tables.
(define (rename-table items)
  (safe-sql *dbh* (sprintf #f "rename table %s to %s"
				 (car items) (cadr items))))

;;i have to save these up, because i have to handle join keys first 
(define (save-table! items)
  (set! *tables* (append *tables* (list items)))) ; list of list here!

;; simple dispatcher, using cute scheme-ism
;; in the file, tables don't have .'s in them, columns do.
;; item is (oldtable.oldcol oldtable.newcol) ... thanks matt :-/
(define (process-change items)
  (if (string-index (car items) #\.)
	  (save-rename-column items *main-schema*)
	  (save-table! items)))


;;; this is basically MAIN, though the load-definition must occur first
(define (load-changes fixfile)
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

;; this is the engine which actually does the work
(define (fix-schema change-alist change-tables schema-alist)
  (for-each rename-column change-alist)
  (for-each rename-table change-tables)		; finally, follow up with tables
  )

;;;;;;;;;;;;;;;
;; main

(define *dbh* (apply simplesql-open "mysql"
				   (read-conf "/mnt/kens/ki/proj/coop/sql/db-fake.conf")))

(load-definition! "/mnt/kens/ki/proj/coop/sql/olddefinition.sql")
(load-changes "/mnt/kens/ki/proj/coop/sql/pcns_schema.txt")

(fix-schema *change-alist* *tables* *main-schema*)

(simplesql-close *dbh*)

;; EOF
