<?php

   // $Id$

require_once('customselect.php');
class HTML_QuickForm_searchselect extends HTML_QuickForm_customselect
{


    function _prepare()
        {
            parent::_prepare();
            $this->setSize(10);
            // XXX this is BROKEN! parent has its own onchange,
            // use addeventlistener instead, maybe even do it in kenflex.js
            $this->_parentForm->updateElementAttr(
                $this->getName(), 
                array('onClick' => "setStatus('')"));
        }

    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $this->_prepare();

            $res = sprintf(
                '<input type="text" name="search-%s" autocomplete="off" 
                onchange="coopSearch(this, \'search-%s\', \'%s\', \'%s\')"/>

                <input  type="button" 
                      onClick="coopSearch(this, \'search-%s\', \'%s\', \'%s\')"
                        value="Search"/> &nbsp;
                <p class="inline" id="status-%s"></p><br>',
                $this->getName(),
                $this->getName(),
                $this->getName(),
                $this->target,
                $this->getName(),
                $this->getName(),
                $target,
                $this->getName()
                );

            $res .= parent::toHtml();
            return $res;
        }
    } //end func toHtml


       function _getJs()
       {
           $jspath = 'lib/flexac';
           // Generate the javascript code needed to handle this element
           $res = '';
           if (!defined('HTML_QUICKFORM_SEARCHSELECT_EXISTS')) {
			   // We only want to include the javascript code once per form
               define('HTML_QUICKFORM_SEARCHSELECT_EXISTS', true);

               // first chain up
               $res  = parent::_getJs();

               $res .= sprintf('<script src="%s/kenflex.js"></script>' , 
                                $jspath);

               $js .= sprintf('
/* begin javascript for HTML_QuickForm_searchselect */
combobox.serverPage="%s/kenflex.php";
%s;
/* end javascript for HTML_QuickForm_searchselect */
               ',
                              $jspath,
                              SID ? 'combobox.SID = "' . SID .'"' : '');
				
			   // wrap wrap wrap wrap it up. i'll take it. up the ying-yang.
               $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
				   $js . "//]]>\n</script>";
           }
           return $res . $js;
       }



	   
}

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('searchselect',
										'lib/searchselect.php', 
										'HTML_QuickForm_searchselect');
}


?>