<?php

// $Id$

require_once('HTML/QuickForm/input.php');

class HTML_QuickForm_isbninput extends HTML_QuickForm_input
{

	var $_parentForm;			// cache
    var $cf; // cache of coopform
    var $access_key; //cache of key
    var $base_url; //cache of baseurl
    var $long_fieldname; //cache of longfieldname
    var $lookup_func_name; //cache of function name

    function prepare(&$parentForm)
        {
            $this->_parentForm =& $parentForm;
            $this->cf =& $this->_parentForm->CoopForm; // save typing
            $this->access_key = COOP_AMAZON_ACCESS_KEY;
            $this->base_url = COOP_ABSOLUTE_URL_PATH;
            $this->lookup_func_name = sprintf(
                "bookLookup('%s', '%s', '%s')", 
                $this->getName(), $this->base_url, $this->access_key);           


            $this->_parentForm->updateElementAttr(
                $this->getName(), 
                array('onchange' => $this->lookup_func_name . '; return false;'));
            
        }


    function _getJs()
        {
            $res = "";
            $res .= $this->cf->page->jsRequireOnce('lib/MochiKit/MochiKit.js',
                                          'INCLUDE_MOCHIKIT');
            $res .= $this->cf->page->jsRequireOnce('lib/booklookup.js',
                                          'INCLUDE_BOOKLOOKUP');
            return $res;
        }



    function toHtml()
        {

            if ($this->_flagFrozen) {
                return $this->getFrozenHTML();
            } else {

                $parent = parent::toHTML(); // the actual {element}!

                /// FINALLY, build the result
                $res = "";
                $res .= sprintf(
                    '&nbsp;<input type="button" value="Lookup" onclick="%s; return false;">&nbsp;<p class="inline" id="status-%s"></p>', 
                    $this->lookup_func_name,
                    $this->getName());
           
                
                return $this->_getJs() . $parent . $res;

            }


        }
    
    
    
} // END CLASS CUSTOMSELECT

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerElementType('isbninput',
                                        'lib/isbninput.php', 
                                        'HTML_QuickForm_isbninput');
}


?>