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
        $this->_prepareSearchSelect();

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            

            $res = sprintf(
                '<input type="text" name="search-%s" autocomplete="off" 
                onchange="combobox_%s.fetchData()"/>

                <input  type="button" 
                      onClick="combobox_%s.fetchData()"
                        value="Search"/> &nbsp;
                <p class="inline" id="status-%s"></p><br>',
                $this->getName(),
                strtr($this->getName(), '-', '_'),
                strtr($this->getName(), '-', '_'),
                $this->getName()
                );

            $res .= parent::toHtml();
            $res .= $this->getSearchSelectJs();
            return $res;
        }
    } //end func toHtml


       function getSearchSelectJs()
       {
           $jspath = 'lib';
           // guard ONLY for the inclusion. the rest must always be done
           $res = '';
           if (!defined('HTML_QUICKFORM_SEARCHSELECT_EXISTS')) {
			   // We only want to include the javascript code once per form
               define('HTML_QUICKFORM_SEARCHSELECT_EXISTS', true);

               $res .= sprintf('<script src="%s/kenflex.js"></script>' , 
                                $jspath);
           }


            list($target, $targfield) = $this->link;
            $target_id =  $target . '-'. $targfield;

               $js .= sprintf('
/* begin javascript for HTML_QuickForm_searchselect */
comboboxsettings.serverPage="%s/kenflex.php";
%s
combobox_%s = new Combobox(\'search-%s\', \'%s\', \'%s\');
/* end javascript for HTML_QuickForm_searchselect */
               ',
                              $jspath,
                          SID ? 'comboboxsettings.SID = "' . SID .'";p' : '',
                          strtr($this->getName(), '-' , '_'),
                          $this->getName(),
                          $this->getName(),
                          $target);
				
			   // wrap wrap wrap wrap it up. i'll take it. up the ying-yang.
               $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
				   $js . "//]]>\n</script>";
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