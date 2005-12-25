<?php

   // $Id$

   require_once('HTML/QuickForm/textarea.php');
   class HTML_QuickForm_mcetextarea extends HTML_QuickForm_textarea
   {
       function toHtml()
       {
           $res  = "";

           // change to tiny_mce_gzip.php, but it doesn't work
           $res .= $this->_parentForm->CoopForm->page->jsRequireOnce('lib/tiny_mce/tiny_mce.js', 
                                                              'COOP_TINYMCE_INCLUDE');
           $res .= wrapJS('tinyMCE.init({
                        	mode : "textareas",
                        convert_newlines_to_brs : true
                });',
                         'HTML_QUICKFORM_MCETEXTAREA_EXISTS');
           
           $res .= "<noscript><h1>NOTICE! Some features on this page require Javascript. You will need to enable Javascript in your browser to use them.</h1></noscript>";


           $res .= parent::toHtml();
		   return $res;
		   
	   } //end func toHtml
	   





    }   // end custommce class


   // took this code from advmultiselect
   if (class_exists('HTML_QuickForm')) {
       HTML_QuickForm::registerElementType('mcetextarea',
										   'lib/mcetextarea.php', 
										   'HTML_QuickForm_mcetextarea');


   }


   ?>