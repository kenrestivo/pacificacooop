;; $Id$

;; experimental scripts to control jwebunit and httpunit via jscheme

(import "com.meterware.httpunit.*")
(define wc (new 'WebConversation))
(define wr (invoke wc 'getResponse "http://www/coop-dev/")) 
(define els (invoke wr 'getElementNames ))
(define links (invoke wr 'getLinks ))
(define forms (invoke wr 'getForms ))

(define form (vector-ref forms 0)) ;; the first one, just for grunts.
(invoke form 'getAction )
(invoke form 'getParameterNames )
(invoke form 'getSubmitButtons )


;; (.getText wr)

;;EOF
