<?php

   // $Id$

require_once('customselect.php');
class HTML_QuickForm_searchselect extends HTML_QuickForm_customselect
{
    var $searchByID = ''; // the LABEL text. i know, nasty hack.

    // XXX HACK! instead, i shoudl be doing some kind of multi-inclusion guard
    function prepare(&$sub)
        {
            // needed before callign parent's prepare!
            $this->showEditText = 1;

            parent::prepare(&$sub);

            $this->setSize(10);

            list($target, $targfield) = $this->link;
            $target_id =  $target . '-'. $targfield;

            if(count($this->vals) < 2){
                $this->addOption('To search, type in box above', '');
            }

        }

    // i am overriding the parents editperms and CONSTRAINING
    // it here. without this little preamble, it will find ALL,
    // which is definitely not what you want in a searchselect!
    function _populateEditPerms($chooseone = false)
        {
            // obviously, only if there's a value there.
            // otherwise, the thing gets populated with EVERYTHING!
            if($this->vals[0] < 1){
                return;
            }

            $this->sub->obj->whereAdd(sprintf('%s.%s = %d', 
                                              $this->sub->table, 
                                              $this->sub->pk,
                                              $this->vals[0]));
            $this->sub->obj->find();
            $this->sub->grouper();
            
            parent::_populateEditPerms(false);
        }



    function toHtml()
    {

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $res = "";

            $brows = $this->cf->page->getBrowserData();
            $autocomplete = $brows['type'] == 'Explorer' ? 'autocomplete="off"' : '';

            // the nifty little js thing. the 1 to fetchdata means by ID
            if(!empty($this->searchByID)){
                $res .= sprintf('%s: <input type="text" size="8" name="byID-%s" %s
                                 onchange="combobox_%s.fetchData()" /><br />',
                                $this->searchByID,
                                $this->getName(),
                                $autocomplete,
                                strtr($this->getName(), '-', '_')
                                );
            }

            // XXX THIS IS IDIOTIC!
            // can i use addelement, please! or at least createElement
            $res .= sprintf(
                '<input type="text" name="search-%s" %s
                onchange="combobox_%s.fetchData()" />

                <input  type="button" 
                      onclick="combobox_%s.fetchData()"
                        value="Search"/> &nbsp;
                <p class="inline" id="status-%s"></p><br />',
                $this->getName(),
                $autocomplete,
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
           $js = "";

         $res .= $this->cf->page->jsRequireOnce(
               sprintf('%s/eventutils.js' , 
                       $jspath),
               'INCLUDE_EVENTUTILS');
         
         $res .= $this->cf->page->jsRequireOnce('lib/MochiKit/MochiKit.js',
                                          'INCLUDE_MOCHIKIT');
         
         $res .= $this->cf->page->jsRequireOnce(
               sprintf('%s/kenflex.js' , 
                       $jspath),
               'INCLUDE_KENFLEX');
           

           
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