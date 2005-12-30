<?php

   // $Id$

require_once('HTML/QuickForm/textarea.php');
class HTML_QuickForm_mcetextarea extends HTML_QuickForm_textarea
{

    function getFrozenHtml()
    {
        return '<div>' . $this->getValue() . '</div>';
    }


    function toHtml()
        {
               
            if ($this->_flagFrozen) {
                return $this->getFrozenHtml();
            }
               
            $res  = "";
               
            // change to tiny_mce_gzip.php, but it doesn't work
            $res .= $this->_parentForm->CoopForm->page->jsRequireOnce('lib/tiny_mce/tiny_mce_gzip.php', 
                                                                      'COOP_TINYMCE_INCLUDE');
            $res .= wrapJS(
                'tinyMCE.init({
  mode : "textareas",
  theme: "advanced",
  theme_advanced_disable: "image,anchor,newdocument,visualaid,link,unlink,code", 
  theme_advanced_buttons3_add: "cut,copy,pasteword,pastetext,selectall",
  convert_newlines_to_brs: true,
  plugins: "paste",
  paste_use_dialog: false,
  paste_auto_cleanup_on_paste: true,     
  paste_strip_class_attributes : "all",
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