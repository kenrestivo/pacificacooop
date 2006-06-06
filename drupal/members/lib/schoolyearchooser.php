<?php

   // $Id$

require_once('HTML/QuickForm/select.php');
class HTML_QuickForm_schoolyearchooser extends HTML_QuickForm_select
{

	var $_parentForm;			// cache


	   
}

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('schoolyearchooser',
										'lib/schoolyearchooser.php', 
										'HTML_QuickForm_schoolyearhcooser');
}


?>