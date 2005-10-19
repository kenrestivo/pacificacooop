<?php

   // $Id$

require_once('HTML/QuickForm/select.php');
class HTML_QuickForm_customselect extends HTML_QuickForm_select
{

	var $_parentForm;			// cache
    var $cf; // cache of coopform
    var $field; //cache of short field name
    var $target; //cache of target table
    var $target_id; // cache of table-targetfield
    var $sub; // cache of sub object
    var $vals; // cache selected values. NEED BECAUSE PHP CAN'T getvalues()[0]!

    function _prepare()
        {

            $this->cf =& $this->_parentForm->CoopForm; // save typing
			list($table, $this->field) = explode('-', $this->getName());


            list($this->target, $targfield) = $this->cf->getLink($this->field);

            //need for perms
            $this->sub =& new CoopObject(&$this->cf->page, $this->target, 
                                         &$this->cf);
            
            //  need these for edit link
            $this->vals = $this->getValue();
            $this->target_id = sprintf('%s-%s', $this->target, $targfield);

            $this->_parentForm->updateElementAttr(
                $this->getName(), 
                array('onchange' => 
                      "processCustomSelect(this, '{$this->target_id}')"));

        }



    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $this->_prepare();


            /// FINALLY, build the result
            $res = "";
            $res .= $this->_getJs();
            $res .= parent::toHTML(); // the actual {element}!
            
            if($this->sub->isPermittedField() >= ACCESS_EDIT){
                $res .= '&nbsp;' . $this->cf->page->selfURL(
                    array(
                        'value' =>sprintf(
                            'Edit',
                            $this->cf->obj->fb_fieldLabels[$this->field]),
                        'par' => false,
                        'elementid' => 'subedit-' . $this->getName(),
                        'inside' => array('table' => $this->target,
                                          'action' => 'edit',
                                          $this->target_id => $this->vals[0],
                                          'push' => $this->getName())));
            }            

            if($this->sub->isPermittedField() >= ACCESS_ADD){
                //XXX do i really need to wrap it in a div? or just use ID?
                $res .= sprintf(
                    '<div>&nbsp;%s</div>',
                    $this->cf->page->selfURL(
                        array(
                            'value' =>sprintf(
                                'Add New %s &gt;&gt;',
                                $this->cf->obj->fb_fieldLabels[$this->field]),
                            'par' => false,
                            'inside' => array('table' => $this->target,
                                              'action' => 'add',
                                              'push' => $this->getName())))
                    );
                
            }

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
function processCustomSelect(selectbox, target_id)
{
   edlink = document.getElementById("subedit-" + selectbox.name);
   if(!edlink){
        return;
   }
   if(selectbox.value > 0){ 
        edlink.className = "";
        // NOTE the THREE GODDAMNED BACKSLASHES HERE IN THE SOURCE CODE!!
        // to keep PHP from mangling it
        re= new RegExp("(" + target_id + "=)\\\d*?(&)", "g");
        edlink.href = edlink.href.replace(re, "$1" + selectbox.value + "$2");
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