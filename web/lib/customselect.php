<?php

   // $Id$

require_once('HTML/QuickForm/select.php');
class HTML_QuickForm_customselect extends HTML_QuickForm_select
{
	var $CoopForm; 			// cache of it

	function toHtml()
		{


			//confessObj($this, 'the customselect');
			list($table, $field) = explode('-', $this->getName());

			// was used in a few places, may be again
			$qfname = sprintf("%s-subtables-%s", $table, $field);

			// the regular selectbox, but with cool stuff
			//TODO: add the _js stuff for showNew()!
			$res .= parent::toHTML();
			$res .= sprintf(
				"&nbsp;<input type=\"submit\" onClick=\"{$this->_jsPrefix}showNew(this.form.elements['%s'])\"  name=\"%s\" value=\"&lt;&lt; Add New\" />", 
				$this->getName(), $qfname);
				


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