
   <?php

   // $Id$

   require_once('HTML/QuickForm/select.php');
   class HTML_QuickForm_advmultselect extends HTML_QuickForm_select
   {
       // Do I even need a constructor?
       /*
       function HTML_QuickForm_advmultselect($elementName=null,
   $elementLabel=null, $text=null, $attributes=null, $values=null)
       {
           $this->HTML_QuickForm_select($elementName, $elementLabel,
   $text, $attributes);
           //$this->setValues($values);
       }
       */
       function toHtml()
       {
           if ($this->_flagFrozen) {
               return $this->getFrozenHtml();
           } else {
               $tabs    = $this->_getTabs();
               $strHtml = '';
               if ($this->getComment() != '') {
                   $strHtml .= $tabs . '<!-- ' . $this->getComment() . "//-->\n";
               }
               // The 'unselected' multi-select which appears on the right
               $strHtmlUnselected = '<select name="__' . $this->getName()
				   . "[]\" size=10 multiple>\n"; // PUT CONFIGURABLE SIZE HERE?
               // The 'selected' multi-select which appears on the left
               $strHtmlSelected = '<select name="_' . $this->getName() .
				   "[]\" size=10 multiple>\n"; // PUT CONFIGURABLE SIZE HERE?
               // The hiddent multi-select
               $strHtmlHidden = '<select name="' . $this->getName() .
				   "[]\" multiple style=\"overflow: hidden; visibility: hidden; display: none; width: 0px; height: 0px;\">\n";

               foreach ($this->_options as $option) {
                   if (is_array($this->_values) &&
					   in_array((string)$option['attr']['value'], $this->_values)) {
                       // The items is *selected* so we want to put it in the 'selected' multi-selectz
                       $strHtmlSelected .= $tabs . "\t<option" .
						   $this->_getAttrString($option['attr']) . '>' .
						   $option['text'] . "</option>\n"; // DO I WANT TO USE THE _getAttrString() METHOD?
                       // Add it to the 'hidden' multi-select and set it as 'selected'
                       $strHtmlHidden .= $tabs . "\t<option" .
						   $this->_getAttrString($option['attr']) . ' selected>' .
						   $option['text'] . "</option>\n";
                   }
                   else {
                       // The item is *unselected* so we want to put it in the 'unselected' multi-select
                       $html = $tabs . "\t<option" .
						   $this->_getAttrString($option['attr']) . '>' .
						   $option['text'] . "</option>\n"; // DO I WANT TO USE THE _getAttrString() METHOD?
                       $strHtmlUnselected .= $html;
                       // Add it to the hidden multi-select as 'unselected'
                       $strHtmlHidden .= $html;
                   }
               }
               $strHtmlSelected .= '</select>';
               $strHtmlUnselected .= '</select>';
               $strHtmlHidden .= '</select>';

               // Get javascript code
               $strHtml .= $this->_getJs();
               // I'm using a table in order to lay this out properly on the page.
               // Any ideas on a better way to do this?
               $strHtml .= $tabs . "<table border=1 cellpadding=1
   cellspacing=0 align=center ><tr><td align=center>\n";
               $strHtml .= $tabs . $strHtmlSelected;
               $strHtml .= $tabs . "</td><td align=center>\n";
               $strHtml .= $tabs . "<input type=\"button\"
   onClick=\"{$this->_jsPrefix}moveSelections(this.form.elements['__" .
				   $this->getName() . "[]'], this.form.elements['_" . $this->getName() .
				   "[]'], this.form.elements['" . $this->getName() . "[]'], 'add')\"
   value=\"<<Add \"><br>\n";
               $strHtml .= $tabs . "<input type=\"button\"
   onClick=\"{$this->_jsPrefix}moveSelections(this.form.elements['__" .
				   $this->getName() . "[]'], this.form.elements['_" . $this->getName() .
				   "[]'], this.form.elements['" . $this->getName() . "[]'], 'remove')\"
   value=\" Remove>> \">\n";
               $strHtml .= $tabs . $strHtmlHidden;
               $strHtml .= $tabs . "</td><td align=center>\n";
               $strHtml .= $tabs . $strHtmlUnselected;
               $strHtml .= $tabs . "</td></tr></table>\n";
			   
               return $strHtml;
           }
       } //end func toHtml
	   
       function _getJs()
       {
           // Generate the javascript code needed to handle this element
           $js = '';
           if (!defined('HTML_QUICKFORM_ADVMULTSELECT_EXISTS')) {
			   // We only want to include the javascript code once per form
               define('HTML_QUICKFORM_ADVMULTSELECT_EXISTS', true);

               $js .= "
                   /* begin javascript for HTML_QuickForm_advmultselect */
                   function {$this->_jsPrefix}moveSelections(menuUnselected, menuSelected, menuHidden, action) {
                       if(action == 'add') {
                           menuFrom = menuUnselected;
                           menuTo = menuSelected;
                       }
                       else {
                           menuFrom = menuSelected;
                           menuTo = menuUnselected;
                       }
                       // Don't do anything if nothing selected. Otherwise we throw jscript errors.
                       if(menuFrom.selectedIndex == -1) {
                           return;
                       }

                       // Add items to the 'TO' list.
                       for ( i=0; i < menuFrom.length; i++) {
                           if (menuFrom.options[i].selected == true ) {
                               menuTo.options[menuTo.length]= new Option(menuFrom.options[i].text, menuFrom.options[i].value);
                           }
                       }

                       // Remove items from the 'FROM' list.
                       for ( i = (menuFrom.length - 1); i>=0; i--){
                           if (menuFrom.options[i].selected == true ) {
                               menuFrom.options[i] = null;
                           }
                       }

                       // Unselect all options in the hidden select.
                       for ( i=0; i < menuHidden.length; i++) {
                           menuHidden.options[i].selected = false;
                       }

                       // Set the appropriate items as 'selected in the hidden select.
                       // These are the values that will actually be posted with the form.
                       for ( i=0; i < menuSelected.length; i++) {
                           menuHidden.options[menuHidden.length] = new Option(menuSelected.options[i].text, menuSelected.options[i].value);
                           menuHidden.options[menuHidden.length-1].selected = true;
                       }
                   }
                   /* end javascript for HTML_QuickForm_advmultselect */
               ";
               $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
				   $js . "//]]>\n</script>";
           }
           return $js;
       }
   }
   // took this code from SelectFilter - much better than messing with QuickForm.php
   if (class_exists('HTML_QuickForm')) {
       HTML_QuickForm::registerElementType('advmultselect',
										   'lib/advmultselect.php', 
										   'HTML_QuickForm_advmultselect');
   }


   ?>
