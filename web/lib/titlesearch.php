<?php

// $Id$

require_once('HTML/QuickForm/input.php');

class HTML_QuickForm_titlesearch extends HTML_QuickForm_input
{

	var $_parentForm;			// cache
    var $cf; // cache of coopform
    var $access_key; //cache of key
    var $base_url; //cache of baseurl
    var $long_fieldname; //cache of longfieldname
    var $lookup_func_params; //cache of params

    function prepare(&$parentForm)
        {
            $this->_parentForm =& $parentForm;
            $this->cf =& $this->_parentForm->CoopForm; // save typing
            $this->access_key = COOP_AMAZON_ACCESS_KEY;
            $this->base_url = COOP_ABSOLUTE_URL_PATH;
            $this->lookup_func_params = sprintf(
                "'%s', '%s', '%s'", 
                $this->getName(), $this->base_url, $this->access_key);           
        }


    function _getJs()
        {
            $res = "";
            $res .= $this->cf->page->jsRequireOnce('lib/MochiKit/MochiKit.js',
                                          'INCLUDE_MOCHIKIT');
            $res .= $this->cf->page->jsRequireOnce('lib/eventutils.js' , 
                                                'INCLUDE_EVENTUTILS');
            $res .= $this->cf->page->jsRequireOnce('lib/booklookup.js',
                                          'INCLUDE_BOOKLOOKUP');


            return $res;
        }

function _postJS()
        {
            // nothing to do here

        }


    function toHtml()
        {

            if ($this->_flagFrozen) {
                return $this->getFrozenHTML();
            } else {


                /// FINALLY, build the result
                $res = "";
                $res .= sprintf('<input type="button" value="Search" onclick="lookupTitle(%s)">
<div class="inline" id="%s"></div><br>
<table class="hidden" id="%s">
	<tr><td>Choose a Title below:</td></tr>
	  <tr>
	  	<td>
			<select id="%s" size="10" name="%s"
				  onchange="showDetails(%s)" >
			</select>
		</td>
		<td class="sidebar" id="%s"></td>
	</tr>
</table>
',
                                $this->lookup_func_params,
                                'status-' . $this->getName(),
                                'lookup-' . $this->getName(),
                                'select-'. $this->getName(),
                                'select-'. $this->getName(),
                                $this->lookup_func_params,
                                'sidebar-'. $this->getName());
                $res .= $this->_postJS();

                $parent = parent::toHTML(); // the actual {element}!

                return $this->_getJs() . $parent . $res;

            }


        }
    
    
    
} // END CLASS CUSTOMSELECT

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerElementType('titlesearch',
                                        'lib/titlesearch.php', 
                                        'HTML_QuickForm_titlesearch');
}


?>