;; $Id$

;; experimental scripts to control jwebunit and httpunit via jscheme
;; (load "/mnt/kens/ki/proj/coop/qa/httpunit-jscheme.scm")

(import "net.sourceforge.jwebunit.*")

(define wtc (new 'WebTestCase))

(define gtc (invoke wtc 'getTestContext))
(invoke gtc 'setBaseUrl "http://www/coop-dev")

;; basic login
(invoke wtc 'beginAt "/")
;; TODO add asserts that this is the RIGHT page
(invoke wtc 'selectOption "auth[uid]" "Restivo Family")
(invoke wtc 'assertSubmitButtonPresent "login")
(invoke wtc 'submit "login")
;; TODO add asserts here that i have the RIGHT page!
(invoke wtc 'setFormElement "auth[pwd]" "tester")
(invoke wtc 'setFormElement "auth[pwd]" "tester")
(invoke wtc 'assertSubmitButtonPresent "login")
(invoke wtc 'assertFormElementPresent "auth[pwd]")
(invoke wtc 'submit "login")


(define dl (invoke wtc 'getDialog))
;;(invoke dl 'getResponseText)

;;EOF
