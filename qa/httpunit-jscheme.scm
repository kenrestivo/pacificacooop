;; $Id$

;; experimental scripts to control jwebunit and httpunit via jscheme
;; (load "/mnt/kens/ki/proj/coop/qa/httpunit-jscheme.scm")


(define wtc (new 'net.sourceforge.jwebunit.WebTestCase))

(define gtc (invoke wtc 'getTestContext))
(invoke gtc 'setBaseUrl "http://www/coop-dev")

;; choose family
(define choose-family
  (lambda(wtc)
	(begin
	  (invoke wtc 'beginAt "/")
	  ;; TODO add asserts that this is the RIGHT page

	  (invoke wtc 'selectOption "auth[uid]" "Restivo Family")
	  (invoke wtc 'assertSubmitButtonPresent "login")
	  (invoke wtc 'submit "login")
	  )))

;;enter password
(define enter-password
  (lambda (wtc)
	(begin
	  (invoke wtc 'assertSubmitButtonPresent "login")
	  (invoke wtc 'assertFormElementPresent "auth[pwd]")

	  (invoke wtc 'setFormElement "auth[pwd]" "tester")
	  (invoke wtc 'setFormElement "auth[pwd]" "tester")
	  (invoke wtc 'submit "login")
	  )))

(define dump-page
  (lambda(wtc)
	(begin
	  (define dl (invoke wtc 'getDialog))
	  (invoke dl 'getResponseText)
	  )))

;;EOF
