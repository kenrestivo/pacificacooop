;; $Id$

(import "com.meterware.httpunit.*")
(define wc (new 'WebConversation))
(define wr (.getResponse wc "http://www/coop-dev/")) 
(define els (.getElementNames wr))
(define links (.getLinks wr))
(define forms (.getForms wr))

(define form (vector-ref forms 0)) ;; the first one, just for grunts.
(.getAction form)
(.getParameterNames form)
(.getSubmitButtons form)


;; (.getText wr)

;;EOF
