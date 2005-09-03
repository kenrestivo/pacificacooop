<?php

   // $Id$

   require_once('HTML/QuickForm/text.php');
   class HTML_QuickForm_customdatebox extends HTML_QuickForm_text
   {
       function toHtml()
       {
		   		   if ($this->_flagFrozen) {
			   return $this->getFrozenHtml();
		   } 
		   $res .= $this->_getTabs();
		   $res .= $this->_getJs();
		   $res .= '<input' . $this->_getAttrString($this->_attributes) . ' />';
		   //  only show this if the field is empty!
		   if(!$this->getValue()){
			   $res .= sprintf(
			   "&nbsp;<input type=\"button\" onClick=\"{$this->_jsPrefix}todaysDate(this.form.elements['%s'])\" value=\"Today\" />", 
			   $this->getName());
		   }
		   
		   return $res;
		   
	   } //end func toHtml
	   
       function _getJs()
       {
           // Generate the javascript code needed to handle this element
           $js = '';
           if (!defined('HTML_QUICKFORM_CUSTOMDATEBOX_EXISTS')) {
			   // We only want to include the javascript code once per form
               define('HTML_QUICKFORM_CUSTOMDATEBOX_EXISTS', true);

               $js .= sprintf('
function %stodaysDate(datefield) {
		var mydate=new Date()
		var theyear=mydate.getYear()
		if (theyear < 1000)
				theyear+=1900
		var theday=mydate.getDay()
		var themonth=mydate.getMonth()+1
		if (themonth<10)
				themonth="0"+themonth
		var theday=mydate.getDate()
		if (theday<10)
				theday="0"+theday

		var displayfirst=themonth
		var displaysecond=theday
		var displaythird=theyear

		datefield.value=displayfirst+"/"+displaysecond+"/"+displaythird
}
               ', $this->_jsPrefix);
               $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
				   $js . "//]]>\n</script>";
           }
			   $js .= "<noscript><h1>NOTICE! Some features on this page require Javascript. You will need to enable Javascript in your browser to use them.</h1></noscript>";
			   return $js;
       }
   }
   // took this code from advmultiselect
   if (class_exists('HTML_QuickForm')) {
       HTML_QuickForm::registerElementType('customdatebox',
										   'lib/customdatebox.php', 
										   'HTML_QuickForm_customdatebox');
   }


   ?>