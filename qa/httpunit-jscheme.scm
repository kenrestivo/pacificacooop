;; $Id$

;; experimental scripts to control jwebunit and httpunit via skij
;; (load "/mnt/kens/ki/proj/coop/qa/httpunit-jscheme.scm")


(define wtc (new 'net.sourceforge.jwebunit.WebTestCase))

(define gtc (invoke wtc 'getTestContext)) ;; the junit object
(invoke gtc 'setBaseUrl "http://www/coop-dev")

;; choose family
(define (choose-family wtc)
	(begin
	  (invoke wtc 'beginAt "/")
	  ;; TODO add asserts that this is the RIGHT page
	  (invoke wtc 'assertTextPresent "</html>")
	  (invoke wtc 'assertFormPresent )
	  (invoke wtc 'assertSubmitButtonPresent "login")
	  (invoke wtc 'selectOption "auth[uid]" "Restivo Family")
	  (invoke wtc 'assertSubmitButtonPresent "login")
	  (invoke wtc 'submit "login")
	  ))

;;enter password
(define (enter-password wtc)
	(begin
	  (invoke wtc 'assertTextPresent "</html>")
	  (invoke wtc 'assertFormPresent )
	  (invoke wtc 'assertSubmitButtonPresent "login")
	  (invoke wtc 'assertFormElementPresent "auth[pwd]")

	  (invoke wtc 'setFormElement "auth[pwd]" "tester")
	  (invoke wtc 'submit "login")
	  ))

(define (main-page-ok wtc)
	(begin
	  (invoke wtc 'assertTextPresent "</html>")
	  (invoke wtc 'assertLinkPresentWithText "Log Out")
	  (invoke wtc 'assertLinkPresentWithText "Enter New")
	  (invoke wtc 'assertLinkPresentWithText "View")
	  ))

(define (dump-page wtc)
	(begin
	(let* ((dl (invoke wtc 'getDialog))) ;; the httpunit object
	  (invoke dl 'getResponseText)
	  )))

(define get-response
  (lambda(wtc)
	(begin
	  (invoke (invoke wtc 'getDialog)
					  'getResponse))))

;; go as far as you can, so far.
(define (get-to-main-page wtc)
	(begin
	  (choose-family wtc)
	  (enter-password wtc)
	  (main-page-ok wtc)))

;; like foreach, cycles through a vector vec, doing procedure.
;; if use-number, pass the procedure the vector's index
;; instead of the vector's object
(define for-each-vector (lambda (procedure vec use-number)
	(do 
		;;variable, init, step (basically, a let)
		( (i 1 (+ 1 i)))
		;;test, expression
		( (>= i (vector-length vec)))
		;; command
		(procedure (if use-number i (vector-ref vec i))))))


;; holder for things i'm still experimenting with.
(define (misc-shit wtc)
	(begin
	  ;; tab stuff
	  (define tabs (invoke (get-response wtc) 'getTables))
	  ;; link stuff
	  (define links (invoke (get-response wtc) 'getLinks))
  (for-each-vector (lambda(v) (pp (invoke v 'getParameterNames)))
					   links  #f)

	  ))
;;EOF
