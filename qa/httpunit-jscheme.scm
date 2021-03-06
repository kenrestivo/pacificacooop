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
(define (choose-family wtc family start-page)
  (invoke wtc 'beginAt start-page)
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
  ;; shirley has no enter (invoke wtc 'assertLinkPresentWithText "Enter New")
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
  (choose-family wtc family "/")
  (enter-password wtc)
  (main-page-ok wtc))


(define (test-generic wtc family)
  (choose-family wtc family "generic.php")
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

(define (check-sublinks links limit)
  (for-each
	 (lambda (link)
	   (let* ((url (invoke link 'getURLString))
			  (link-text (invoke link 'asText))
; 			 (save-port (open-output-file
; 						 (string-append "/mnt/kens/ki/proj/coop/qa/reports/"
; 						  (number->string run-number) family
; 						  (string-replace url  #\/ #\-)
; 						  )))
			 )
		 ;; TODO: test here for email instead of skipping at end
		 (write-line (string-append 
					  link-text " > "
					  url))
		 (invoke link 'click)
		 ;; save the raw html i got, so i can see what puked!
; 		 (display (dump-page wtc) save-port)
; 		 (close-output-port save-port)
		 (invoke wtc 'assertTextPresent "</html>")

         ;; TODO: now visit the first Details and Enter New on each
		 
		 ;;(validate-html wtc)
		 ))
		 ;; skip main menu (first two) and email (last)
		 (cddr (list-head links
					  (- (length links) 1)))))


(define (visit-all-links wtc family url)
  (let ((gtc (invoke wtc 'getTestContext))) ;; the junit object
	(invoke gtc 'setBaseUrl url))
  (get-to-main-page wtc family)
  (let ((links (vector->list (invoke (get-response wtc) 'getLinks)) )
		(run-number (random 1000)))
    (check-sublinks links 0))
  (test-generic wtc family))

;;or http://validator.w3.org/check
(define (validate-html wtc)
  (let* ((html (dump-page wtc))
		 (result (post-url "http://fred/w3c-markup-validator/check"
			  (list (list "uploaded_file" html) '("ss" "1")))))
	;; TODO: check it for <h2 id="result" class="valid"> 
    ;; though id is NOT present on invalid
	;; cheap substring? why not.
	;; or figure out how to inject html into a response and then parse it
	;; and save it if it's not present
	))

;; a silly driver around visit-all-links
(define (many-visit-hack wtc url)
  (for-each (lambda (family)
			  (write-line (string-append ".....checking " family))
			  (visit-all-links wtc (string-append family " Family") url))
			'("Bartlett" "Restivo" "Cooke" ))
  (for-each (lambda (other)
			  (write-line (string-append ".....checking " other))
			  (visit-all-links wtc other url))
			'("Teacher Sandy" "Shirley" ))
  )

;; i.e.; (many-visit-hack wtc "http://www/coop-dev") 

;;EOF
