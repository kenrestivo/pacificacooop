
   <?php

   // $Id$

   require_once('HTML/QuickForm/select.php');
   class HTML_QuickForm_customselect extends HTML_QuickForm_select
   {
	   function toHtml()
		   {
			   $res .= parent::toHTML();
			   $res .= sprintf(
				   "&nbsp;<input type=\"button\" onClick=\"{$this->_jsPrefix}showNew(this.form.elements['%s'])\" value=\"New\" />", 
			   $this->getName());

			   return $res;
		   }
	   
   }

   // took this code from advmultiselect
   if (class_exists('HTML_QuickForm')) {
       HTML_QuickForm::registerElementType('customselect',
										   'lib/customselect.php', 
										   'HTML_QuickForm_customselect');
   }


   ?>
