<?php

   // $Id$

require_once('HTML/QuickForm/select.php');
class HTML_QuickForm_customselect extends HTML_QuickForm_select
{

	function toHtml()
		{

			// the regular selectbox, but with cool stuff
			//TODO: add the _js stuff for showNew()!		

			$res .= parent::toHTML();
			$res .= "&nbsp;";
	
			// TODO put the button in
	
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