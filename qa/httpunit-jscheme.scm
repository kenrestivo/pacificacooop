;; $Id$

(import "com.meterware.httpunit.*")
(define wc (new 'WebConversation))
(define wr (.getResponse wc "http://www/coop-dev/")) 

