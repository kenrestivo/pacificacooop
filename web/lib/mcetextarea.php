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
            $res .= $this->_parentForm->CoopForm->page->jsRequireOnce(
                COOP_ABSOLUTE_URL_PATH . '/lib/tiny_mce/tiny_mce_gzip.php', 
                'COOP_TINYMCE_INCLUDE');

            $res .= $this->_parentForm->CoopForm->page->jsRequireOnce(
                empty($this->_parentForm->CoopForm->obj->fb_mceInitFile)? 
                COOP_ABSOLUTE_URL_PATH . '/lib/tinymceinit.js' : 
                $this->_parentForm->CoopForm->obj->fb_mceInitFile, 
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