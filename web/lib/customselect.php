
   <?php

   // $Id$

   require_once('HTML/QuickForm/select.php');
   class HTML_QuickForm_customselect extends HTML_QuickForm_select
   {
	   function toHtml()
		   {
			   $res .= parent::toHTML();
			   list($table, $field) = explode('-', $this->getName());

			   //TODO: add the _js stuff for showNew()!
			   $res .= sprintf(
				   "&nbsp;<input type=\"submit\" onClick=\"{$this->_jsPrefix}showNew(this.form.elements['%s'])\"  name=\"%s-subtables[%s]\" value=\"&lt;&lt; Add New\" />", 
			   $this->getName(), $table, $field);

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
