<?php

   // $Id$

require_once('HTML/QuickForm/select.php');
class HTML_QuickForm_customselect extends HTML_QuickForm_select
{

	var $_parentForm;			// cache

    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $cf =& $this->_parentForm->CoopForm; // save typing
            $values = $this->_parentForm->exportValues();
			list($table, $field) = explode('-', $this->getName());
            //confessArray($values, 'values');

            // TODO: go get the object or name of the linkfield

            if(isset($cf->forwardLinks[$field])){
                $link =$cf->forwardLinks[$field];
            } else {
                $link = $cf->backLinks[$field];
            }
            list($target, $targfield) = explode(':', $link);

            //XXX didn't i do this somewhere else already?
            $sub =& new CoopObject(&$cf->page, $target, &$cf);
            
            $sub =& new CoopObject(&$cf->page, $target, &$cf);
            if($sub->isPermittedField() < ACCESS_ADD){
                return  parent::toHTML(); // the actual {element}!
            }

			return 
                sprintf('%s <div class="sublink">&nbsp;%s</div>',
                        parent::toHTML(), // the actual {element}!
                        $cf->page->selfURL(
                            array(
                                'value' =>sprintf('Add New %s &gt;&gt;',
                                                  $sub->obj->fb_shortHeader),
                                'par' => false,
                                'inside' => array('table' => $target,
                                                  'action' => 'add',
                                                  'push' => $this->getName())
                                )));

        }
    } //end func toHtml


	   
}

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('customselect',
										'lib/customselect.php', 
										'HTML_QuickForm_customselect');
}


?>