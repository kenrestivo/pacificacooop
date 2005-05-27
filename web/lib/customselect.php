<?php

   // $Id$

require_once('HTML/QuickForm/select.php');
class HTML_QuickForm_customselect extends HTML_QuickForm_select
{
	var $CoopForm; 			// cache of it
	var $_parentForm;			// cache
	var $qfname;


	function reallyCreate(&$form)
		{
			if ($form !== null) {
				$this->_parentForm =& $form;
			} 

		
		list($table, $field) = explode('-', $this->getName());
		
		// was used in a few places, may be again
		$this->qfname = sprintf("%s-subtables-%s", $table, $field);

		//confessObj($this->_parentForm, 'parentform');
 
		// yeah, sure
 		$this->_parentForm->addElement('submit', $this->qfname, 
 									   "<< Add New");
			
		}

	function toHtml()
		{

			// the regular selectbox, but with cool stuff
			//TODO: add the _js stuff for showNew()!
			$res .= parent::toHTML();
			$res .= "&nbsp;";
			
			// put this back when i figure out how to do it
 			//$el =& $this->_parentForm->getElement($this->qfname);
 			//$res .= $el->toHTML();

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