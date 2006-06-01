;; $Id$
;; silly script to fix my filenames

(defun uncoop () 
  "move files around naming-wise"
  (interactive)
  (mark-paragraph)
  (replace-string "Coop" "COOP/" nil (region-beginning) (region-end)))
