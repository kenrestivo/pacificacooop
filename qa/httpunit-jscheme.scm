;; $Id$

;; experimental scripts to control jwebunit and httpunit via jscheme

(import "net.sourceforge.jwebunit.*")

(define wtc (new 'WebTestCase))

(define gtc (invoke wtc 'getTestContext))
(invoke gtc 'setBaseUrl "http://www/coop-dev")

(invoke wtc 'beginAt "/")

;;EOF
