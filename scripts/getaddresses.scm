;;; $Id$
;; put the parentid in for all the solicitation auction and money
;; one-off script to handle my having stuck the emails in the wrong place
;; (load "/mnt/kens/ki/proj/coop/scripts/getaddresses.scm")

(use-modules (ice-9 slib)
			 (kenlib)
			 (database simplesql))
(require 'printf)


(define *dbh* (apply simplesql-open "mysql"
					 (read-conf
					  "/mnt/kens/ki/proj/coop/sql/db-input.conf")))

(define *addresses* '())


;;; for loading 'em
(define (get-addresses)
 (set! *addresses* '())
  (for-each
    (lambda (file-name)
      (let ((p (open-input-file file-name)))
        (do ((line (read-line p) (read-line p)))
            ((or (eof-object? line)))
          (let ((pl (parse-csv line)))
            (set! *addresses*
              (append
                *addresses*
                (list (list (list-ref pl 0)
                            (list-ref pl 3)
							;; TODO format birthdates too
                            (list-ref pl 5)))))))
        (close p)))
    '("/mnt/kens/ki/proj/coop/imports/am-11-2003.csv"
      "/mnt/kens/ki/proj/coop/imports/pm-11-2003.csv")))

(define (dump-into-temp-table)
  (for-each
    (lambda (address-line)
      (safe-sql
        *dbh*
        (sprintf
          #f
          "insert into temp set
						last_name = '%s',
						first_name = '%s',
						address = '%s'"
          (list-ref address-line 0)
          (list-ref address-line 1)
          (list-ref address-line 2))))
    *addresses*))



(simplesql-close *dbh*)

;;; EOF
