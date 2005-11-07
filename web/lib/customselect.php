<?php

// $Id$

require_once('HTML/QuickForm/select.php');


class HTML_QuickForm_customselect extends HTML_QuickForm_select
{

	var $_parentForm;			// cache
    var $cf; // cache of coopform
    var $field; //cache of short field name
    var $link; //cache of the link array
    var $sub; // cache of sub object
    var $vals; // cache selected values. NEED BECAUSE PHP CAN'T getvalues()[0]!
    var $showEditText = 0; // the edit link show the text USE 1/0 NOT TRUE/FALSE

    function prepare()
        {
            $this->cf =& $this->_parentForm->CoopForm; // save typing
			list($table, $this->field) = explode('-', $this->getName());


            $this->link = explode(':', $this->cf->forwardLinks[$this->field]);

            list($target, $targfield) = $this->link;
            $target_id =  $target . '-'. $targfield;

            //need for perms
            $this->sub =& new CoopObject(&$this->cf->page, $target, 
                                         &$this->cf);


            
            //  need these for edit link
            $this->vals = $this->getValue();

            $this->_parentForm->updateElementAttr(
                $this->getName(), 
                array('onchange' => 
                      "processCustomSelect(this, '{$target_id}', 
                                                {$this->showEditText})"));

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
                $res .= parent::toHTML(); // the actual {element}!
            
                if($this->sub->isPermittedField(null, true, true) >= ACCESS_EDIT)
                {
                    $res .= '&nbsp;' . $this->cf->page->selfURL(
                        array(
                            'value' =>sprintf(
                                'Edit',
                                $this->cf->obj->fb_fieldLabels[$this->field]),
                            'par' => false,
                            'elementid' => 'subedit-' . $this->getName(),
                            'inside' => array('table' => $target,
                                              'action' => 'edit',
                                              $target_id => $this->vals[0],
                                              'push' => $this->getName())));
                    // TODO: add a hidden field with the JSON perms array
                }            

                if($this->sub->isPermittedField(null, true, true) >= ACCESS_ADD)
                {
                    //XXX do i really need to wrap it in a div? or just use ID?
                    $res .= sprintf(
                        '<div>&nbsp;%s</div>',
                        $this->cf->page->selfURL(
                            array(
                                'value' =>sprintf(
                                    'Add New %s &gt;&gt;',
                                    $this->cf->obj->fb_fieldLabels[$this->field]),
                                'par' => false,
                                'inside' => array('table' => $target,
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


            // specific to this select
            // rebuild the table/field because i can't use - in globals!
            // XXX this typeof is a hack to get around bug in httpunit
            // the right way would be to make this an onload or something
            $js .= sprintf(
                'if(typeof document.getElementsByName == \'function\') {
                  eval(\'editperms_%s_%s = \' + document.getElementsByName(\'editperms-%s\')[0].value);
                 }', 
                $this->cf->table, $this->field,
                $this->getName());


            // non-specific
            if (!defined('HTML_QUICKFORM_CUSTOMSELECT_EXISTS')) {
                // We only want to include the javascript code once per form
                define('HTML_QUICKFORM_CUSTOMSELECT_EXISTS', true);
                
                $js .= '
/* begin javascript for HTML_QuickForm_customselect */
function processCustomSelect(selectbox, target_id, showtext)
{
   edlink = document.getElementById("subedit-" + selectbox.name);
   if(!edlink){
        return;
   }
   // XXX this is a global, and, that sucks
   editperms = eval(\'editperms_\' + selectbox.name.replace(/-/g, \'_\'));

   if(selectbox.value > 0 && editperms[selectbox.value]){ 
        edlink.className = "";
        // NOTE the THREE GODDAMNED BACKSLASHES HERE IN THE SOURCE CODE!!
        // to keep PHP from mangling it
        re= new RegExp("(" + target_id + "=)\\\d*?(&)", "g");
        edlink.href = edlink.href.replace(re, "$1" + selectbox.value + "$2");
        if(showtext){
               edlink.innerHTML = "Edit " + 
                        selectbox.options[selectbox.selectedIndex].text;
        }
   } else {
        edlink.className = "hidden";
   }

}
/* end javascript for HTML_QuickForm_customselect */
               ';
				
            }
            
            $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
                $js . "//]]>\n</script>";
 $js .= '<noscript><h1>WARNING! This page WILL NOT work without Javascript. You must enable Javascript in your browser first. Sorry about that.</h1></noscript>';
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