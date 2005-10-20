<?php

   // $Id$

require_once('customselect.php');
class HTML_QuickForm_searchselect extends HTML_QuickForm_customselect
{

    // XXX HACK! instead, i shoudl be doing some kind of multi-inclusion guard
    function _prepareSearchSelect()
        {
            // needed before callign parent's prepare!
            $this->showEditText = 1;

            parent::_prepare();

            $this->setSize(10);

            list($target, $targfield) = $this->link;
            $target_id =  $target . '-'. $targfield;

            // XXX this is BROKEN! parent has its own onchange,
            // use addeventlistener instead, maybe even do it in kenflex.js
            $this->_parentForm->updateElementAttr(
                $this->getName(), 
                array('onClick' => "setStatus('')"));


            //TODO: if there is a value present, and it's NOT in options,
            //then go fetch the option and add it here
            if(array_sum($this->vals)){
                $this->sub->obj->{$targfield} = $this->vals[0];
                $this->sub->obj->find();
                $options = $this->cf->getLinkOptions($this->sub, 
                                                     $this->link, 
                                                     false);
                $this->cf->page->confessArray(
                    $options, "CoopForm::Link options($table $targfield)", 4);
                $this->loadArray($options);
            }
        }

    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $this->_prepareSearchSelect();
            
            list($target, $targfield) = $this->link;
            $target_id =  $target . '-'. $targfield;


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
                $target,
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