;; $Id$
;; squile script to convert unassigned auctions into their own packages

(use-modules (ice-9 slib))
(require 'pretty-print)

;; unassigned-auctions
(define (unassigned-auctions cid)
  (sql-query cid
			 "select auctionid, description, amount
				from auction
				where package_id < 1
					and date_received > '0000-00-00'"))

;;;; last-insert-id
(define (last-insert-id cid)
  (vector-ref
	(vector-ref
	 (sql-query cid "select last_insert_id()") 1) 0))

;;;; for-each-vector
(define for-each-vector (lambda (procedure vec use-number)
	(do 
		;;variable, init, step (basically, a let)
		( (i 1 (+ 1 i)))
		;;test, expression
		( (>= i (vector-length vec)))
		;; command
		(procedure (if use-number i (vector-ref vec i))))))

;;; new-package
(define new-package
  (lambda (cid auctionvec)
	(begin
	  ;;; XXX finish me
	  )))

;;;; MAIN

(define cid (sql-create "coop"  "bc" "input" "test"))



;; EOF
