<?php

// $Id$

require_once('HTML/QuickForm/input.php');

class HTML_QuickForm_isbninput extends HTML_QuickForm_input
{

	var $_parentForm;			// cache
    var $cf; // cache of coopform
    var $field; //cache of short field name
    var $link; //cache of the link array
    var $sub; // cache of sub object
    var $vals; // cache selected values. NEED BECAUSE PHP CAN'T getvalues()[0]!

    function prepare(&$sub)
        {
            $this->sub =& $sub;
            $this->cf =& $this->_parentForm->CoopForm; // save typing


			list($table, $this->field) = explode('-', $this->getName());


            $this->link = explode(':', $this->cf->forwardLinks[$this->field]);

            list($target, $targfield) = $this->link;
            $target_id =  $target . '-'. $targfield;


            
            //  need these for edit link
            $this->vals = $this->getValue();

            $func = "processISBN(this, '{$target_id}')";

            $this->_parentForm->updateElementAttr(
                $this->getName(), 
                array('onkeyup' => $func,
                      'onchange' => $func));



        }



    function toHtml()
        {

            if ($this->_flagFrozen) {
                return $this->getFrozenHTML();
            } else {

                list($target, $targfield) = $this->link;
                $target_id =  $target . '-'. $targfield;


                /// FINALLY, build the result
                $res = "";
                $res .= $this->_getJs();
                $parent = parent::toHTML(); // the actual {element}!

            }




            
	   
    } // END CLASS CUSTOMSELECT

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('isbninput',
										'lib/isbninput.php', 
										'HTML_QuickForm_isbninput');
}


?>