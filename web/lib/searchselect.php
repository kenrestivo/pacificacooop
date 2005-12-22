<?php

   // $Id$

require_once('customselect.php');
class HTML_QuickForm_searchselect extends HTML_QuickForm_customselect
{

    // XXX HACK! instead, i shoudl be doing some kind of multi-inclusion guard
    function prepare()
        {
            // needed before callign parent's prepare!
            $this->showEditText = 1;

            parent::prepare();

            $this->setSize(10);

            list($target, $targfield) = $this->link;
            $target_id =  $target . '-'. $targfield;

            if(count($this->vals) < 2){
                $this->addOption('To search, type in box above', '');
            }

        }

    function toHtml()
    {

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            

            $res = sprintf(
                '<input type="text" name="search-%s" autocomplete="off" 
                onchange="combobox_%s.fetchData()"/>

                <input  type="button" 
                      onclick="combobox_%s.fetchData()"
                        value="Search"/> &nbsp;
                <p class="inline" id="status-%s"></p><br />',
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
           $res = '';
           $res .= $this->cf->page->jsRequireOnce(
               sprintf('%s/kenflex.js' , 
                       $jspath),
               'HTML_QUICKFORM_SEARCHSELECT_EXISTS');
           

           
           list($target, $targfield) = $this->link;
           $target_id =  $target . '-'. $targfield;

           // i don't wrap this in an inclusin guard, may have > 1
           $js .= sprintf('
/* begin javascript for THIS PARTICULAR HTML_QuickForm_searchselect */
comboboxsettings.serverPage="%s/kenflex.php";
%s
combobox_%s = new Combobox(\'search-%s\', \'%s\', \'%s\');
/* end javascript for THIS PARTICULAR HTML_QuickForm_searchselect */
               ',
                              $jspath,
                          SID ? 'comboboxsettings.SID = "' . SID .'";' : '',
                          strtr($this->getName(), '-' , '_'),
                          $this->getName(),
                          $this->getName(),
                          $target);
				
			   // wrap wrap wrap wrap it up. i'll take it. up the ying-yang.
           return $res . wrapJS($js);
       }



	   
}

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('searchselect',
										'lib/searchselect.php', 
										'HTML_QuickForm_searchselect');
}


?>