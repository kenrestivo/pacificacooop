;; $Id$

;; experimental scripts to control jwebunit and httpunit via skij
;; (load "/mnt/kens/ki/proj/coop/qa/httpunit-jscheme.scm")


(define wtc (new 'net.sourceforge.jwebunit.WebTestCase))

(define gtc (invoke wtc 'getTestContext)) ;; the junit object
(invoke gtc 'setBaseUrl "http://www/coop-dev")

;; choose family
(define choose-family
  (lambda(wtc)
	(begin
	  (invoke wtc 'beginAt "/")
	  ;; TODO add asserts that this is the RIGHT page
	  (invoke wtc 'assertTextPresent "</html>")
	  (invoke wtc 'assertFormPresent )
	  (invoke wtc 'assertSubmitButtonPresent "login")

	  (invoke wtc 'selectOption "auth[uid]" "Restivo Family")
	  (invoke wtc 'assertSubmitButtonPresent "login")
	  (invoke wtc 'submit "login")
	  )))

;;enter password
(define enter-password
  (lambda (wtc)
	(begin
	  (invoke wtc 'assertTextPresent "</html>")
	  (invoke wtc 'assertFormPresent )
	  (invoke wtc 'assertSubmitButtonPresent "login")
	  (invoke wtc 'assertFormElementPresent "auth[pwd]")

	  (invoke wtc 'setFormElement "auth[pwd]" "tester")
	  (invoke wtc 'setFormElement "auth[pwd]" "tester")
	  (invoke wtc 'submit "login")
	  )))

(define main-page-ok
  (lambda (wtc)
	(begin
	  (invoke wtc 'assertTextPresent "</html>")
	  (invoke wtc 'assertLinkPresentWithText "Log Out")
	  (invoke wtc 'assertLinkPresentWithText "Enter New")
	  (invoke wtc 'assertLinkPresentWithText "View")
	  )))

(define dump-page
  (lambda(wtc)
	(begin
	(let* ((dl (invoke wtc 'getDialog))) ;; the httpunit object
	  (invoke dl 'getResponseText)
	  ))))

(define get-response
  (lambda(wtc)
	(begin
	  (invoke (invoke wtc 'getDialog)
					  'getResponse))))

;;(define tab ((invoke (get-response wtc) 'getTableStartingWith "Description")) 'asText)

;;EOF
