
   <?php

   // $Id$

   require_once('HTML/QuickForm/text.php');
   class HTML_QuickForm_customdatebox extends HTML_QuickForm_text
   {
       function toHtml()
       {
		   confessObj($this, 'formelement');
		   if ($this->_flagFrozen) {
			   return $this->getFrozenHtml();
		   } 
		   $res .= $this->_getTabs();
		   $res .= $this->_getJs();
		   $res .= '<input' . $this->_getAttrString($this->_attributes) . ' />';
		   $res .= sprintf(
			   '&nbsp;<a href="javascript:todaysDate(\'%s\',\'%s\')">Insert Today\'s Date</a>', 
			   $this->getName(), $formname);
		   
		   return $res;
		   
	   } //end func toHtml
	   
       function _getJs()
       {
           // Generate the javascript code needed to handle this element
           $js = '';
           if (!defined('HTML_QUICKFORM_CUSTOMDATEBOX_EXISTS')) {
			   // We only want to include the javascript code once per form
               define('HTML_QUICKFORM_CUSTOMDATEBOX_EXISTS', true);

               $js .= '
function todaysDate(fieldname,formname) {
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

		document.forms[formname][fieldname].value=displayfirst+"/"+displaysecond+"/"+displaythird
}
               ';
               $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
				   $js . "//]]>\n</script>";
           }
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
