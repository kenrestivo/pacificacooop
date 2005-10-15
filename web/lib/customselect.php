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
            $res = "";
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

            $this->_parentForm->updateElementAttr(
                $this->getName(), 
                array('onchange' => 'processCustomSelect(this)'));
            
            // it's an array! need this for edit
            $vals = $this->getValue();

            $res .= $this->_getJs();
            $res .= parent::toHTML(); // the actual {element}!
            
            $res .= '&nbsp;' . $cf->page->selfURL(
                array(
                    'value' =>sprintf(
                        'Edit',
                        $cf->obj->fb_fieldLabels[$field]),
                    'par' => false,
                    'elementid' => 'subedit-' . $this->getName(),
                    'inside' => array('table' => $target,
                                      'action' => 'edit',
                                      sprintf('%s-%s',
                                              $target, $targfield) => $vals[0],
                                      'push' => $this->getName())));
            

            $res .= sprintf('<div>&nbsp;%s</div>',
                            $cf->page->selfURL(
                                array(
                                    'value' =>sprintf(
                                        'Add New %s &gt;&gt;',
                                        $cf->obj->fb_fieldLabels[$field]),
                                    'par' => false,
                                    'inside' => array('table' => $target,
                                                      'action' => 'add',
                                                      'push' => $this->getName())))
                );
            return $res;
        }
    } //end func toHtml


       function _getJs()
       {
           // Generate the javascript code needed to handle this element
           $js = '';
           if (!defined('HTML_QUICKFORM_CUSTOMSELECT_EXISTS')) {
			   // We only want to include the javascript code once per form
               define('HTML_QUICKFORM_CUSTOMSELECT_EXISTS', true);

               $js .= '
/* begin javascript for HTML_QuickForm_customselect */
function processCustomSelect(selectbox)
{
   edlink = document.getElementById("subedit-" + selectbox.name);
   if(selectbox.value > 0){ 
        edlink.className = "";
   } else {
        edlink.className = "hidden";
   }

}
/* end javascript for HTML_QuickForm_customselect */
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
	HTML_QuickForm::registerElementType('customselect',
										'lib/customselect.php', 
										'HTML_QuickForm_customselect');
}


?>