<?php

   // $Id$

require_once('customselect.php');
class HTML_QuickForm_searchselect extends HTML_QuickForm_customselect
{

	var $_parentForm;			// cache

    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $res = parent::toHtml();
            $res .= 'FOO!';
            return $res;
        }
    } //end func toHtml


       function _getJs()
       {
           // Generate the javascript code needed to handle this element
           $res = '';
           if (!defined('HTML_QUICKFORM_SEARCHSELECT_EXISTS')) {
			   // We only want to include the javascript code once per form
               define('HTML_QUICKFORM_SEARCHSELECT_EXISTS', true);

               // first chain up
               $res  = parent::_getJs();


               $js .= '
/* begin javascript for HTML_QuickForm_searchselect */
/* end javascript for HTML_QuickForm_searchselect */
               ';
				
			   // wrap wrap wrap wrap it up. i'll take it. up the ying-yang.
               $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
				   $js . "//]]>\n</script>";
           }
           return $js;
       }



	   
}

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('searchselect',
										'lib/searchselect.php', 
										'HTML_QuickForm_searchselect');
}


?>