;; $Id$
;; squile script to convert unassigned auctions into their own packages

;; LAUNCH (load "/mnt/kens/ki/proj/coop/scripts/import_packages.scm")

(use-modules (ice-9 slib))
(require 'pretty-print)

;; TODO make that a proper library!
(load "/mnt/kens/ki/is/scheme/lib/kenlib.scm")

;; unassigned-auctions
(define (unassigned-auctions cid)
  (sql-query cid
			 "select auctionid, description, amount
				from auction
				where (package_id is null or package_id < 1)
					and date_received > '0000-00-00'"))

;;; new-package
(define (add-new-package cid auctionvec)
	  ;;; XXX finish me
	 nil)

;;;;;;;;;;;;;;;;;;;;
;;;; MAIN

;; startup
(define cid (sql-create "coop"  "bc" "input" "test"))

(sql-query cid "select packages.* from packages left join auction using (package_id) where auction.package_id is null")

;; EOF
