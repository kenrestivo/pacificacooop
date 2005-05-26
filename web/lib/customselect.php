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

			// used in a few places
			$qfname = sprintf("%s-subtables-%s", $table, $field);

			// find out what subforms have been requested iwith ADD NEW
			// NOTE MUST use submit for return trips!
			// because this hidden is NOT part of $this
			$vars =& $this->CoopForm->form->getSubmitValues();
			if(isset($vars[$qfname])){  
				// NOTE i'm already at tohtml, so the coopform cache is useless
				$sub =& $this->CoopForm->addSubtable($field);

				// so that it stays expanded ;-)
				// MUST add it to subform!
				// by the time i'm at toHTML(), it's too late for top form
				$sub->form->addElement('hidden', 
												  $qfname, 'pass-through');

				//GOTTA do this shit here! can't just $sub->form->toHTML()!
				require_once('HTML/QuickForm/Renderer/Default.php');
				$renderer = new HTML_QuickForm_Renderer_Default();
				$sub->form->accept($renderer);
				
 				$res .= sprintf(
 					'<div id="%s">%s</div>', 
 					$this->getName(),
 					preg_replace('!</?form[^>]*?>!i', '',
								 $renderer->toHTML()));
				
			} else {
				// the regular selectbox, but with cool stuff
				//TODO: add the _js stuff for showNew()!
				$res .= parent::toHTML();
				$res .= sprintf(
					"&nbsp;<input type=\"submit\" onClick=\"{$this->_jsPrefix}showNew(this.form.elements['%s'])\"  name=\"%s\" value=\"&lt;&lt; Add New\" />", 
					$this->getName(), $qfname);
				
			}

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