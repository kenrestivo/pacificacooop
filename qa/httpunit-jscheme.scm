;; $Id$

;; experimental scripts to control jwebunit and httpunit via skij
;; (load "/mnt/kens/ki/proj/coop/qa/httpunit-jscheme.scm")

;; use proper libraries!! figure out how.
(load "/mnt/kens/ki/is/scheme/lib/kenlib-generic.scm")
(load "/mnt/kens/ki/is/scheme/lib/post-url-skij.scm")

(define wtc (new 'net.sourceforge.jwebunit.WebTestCase))

(define gtc (invoke wtc 'getTestContext)) ;; the junit object
(invoke gtc 'setBaseUrl "http://www/coop-dev")

;; choose family
(define (choose-family wtc family)
  (invoke wtc 'beginAt "/")
  ;; TODO add asserts that this is the RIGHT page
  (invoke wtc 'assertTextPresent "</html>")
  (invoke wtc 'assertFormPresent )
  (invoke wtc 'assertSubmitButtonPresent "login")
  (invoke wtc 'selectOption "auth[uid]" family)
  (invoke wtc 'assertSubmitButtonPresent "login")
  (invoke wtc 'submit "login")
  )

;;enter password
(define (enter-password wtc)
  (invoke wtc 'assertTextPresent "</html>")
  (invoke wtc 'assertFormPresent )
  (invoke wtc 'assertSubmitButtonPresent "login")
  (invoke wtc 'assertFormElementPresent "auth[pwd]")

  (invoke wtc 'setFormElement "auth[pwd]" "tester")
  (invoke wtc 'submit "login")
  )

(define (main-page-ok wtc)
  (invoke wtc 'assertTextPresent "</html>")
  (invoke wtc 'assertLinkPresentWithText "Log Out")
  (invoke wtc 'assertLinkPresentWithText "Enter New")
  (invoke wtc 'assertLinkPresentWithText "View")
  )

;;; the "dialog" is the httpunit object, basically
(define (dump-page wtc)
  (let* ((dl (invoke wtc 'getDialog))) ;; the httpunit object
	(invoke dl 'getResponseText)
	))

(define (get-response wtc)
	(invoke (invoke wtc 'getDialog)
			'getResponse))

;; go as far as you can, so far.
(define (get-to-main-page wtc family)
  (choose-family wtc family)
  (enter-password wtc)
  (main-page-ok wtc))


;; holder for things i'm still experimenting with.
(define (misc-shit wtc)
  ;; tab stuff
  (define tabs (invoke (get-response wtc) 'getTables))
  ;; link stuff
  (define links (invoke (get-response wtc) 'getLinks))
  (for-each-vector
   (lambda(v) (pp (invoke v 'getParameterNames)))
   links  #f)

  )

(define (visit-all-links wtc family)
  (get-to-main-page wtc family)
  (let ((links (vector->list (invoke (get-response wtc) 'getLinks)) ))
	(for-each
	 (lambda (link)
	   (let ((url (invoke link 'getURLString)))
		 ;; test here for email
		 (invoke link 'click) (write-line (string-append
										   (invoke link 'asText) " > "
										   url))
		 (invoke wtc 'assertTextPresent "</html>")
		 ;;(validate-html wtc)
		 ))
		 ;; skip main menu (first two) and email (last)
		 (cddr (list-head links
					  (- (length links) 1))))))


(define (validate-html wtc)
  (let ((html (dump-page wtc)))
	(post-url "http://fred/w3c-markup-validator/check"
			  (list (list "uploaded_file" html) '("ss" "1")))
	;; TODO: check it for <h2 id="result" class="valid">
	;; and save it if it's not present
	))


;;EOF
