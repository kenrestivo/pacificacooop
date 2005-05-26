<?php

   // $Id$


require_once 'HTML/QuickForm/Action/Display.php';


class CustomDisplay extends HTML_QuickForm_Action_Display
{
	function _renderForm(&$page)
		{
			// i hate this. but, should only be one display/page anyway
			print $page->controller->cp->flushBuffer();
			print parent::_renderForm($page);

		}



}


class CoopQuickForm_Page extends HTML_QuickForm_Page
{

    function CoopQuickForm_Page($formName, $method = 'post', $target = '_self', 
					 $attributes = null)
    {
		// MUST tracksubmit, or BAAAAD things happen
		// so i don't call parent (HTML_QuickForm_Page) i call HTML_QuickForm
        $this->HTML_QuickForm($formName, $method, '', $target, $attributes, 
							  true);
		
	}

	function validate()
		{

			if(is_object($this->CoopForm)){
				// MUST send true arg to validate, or it recurses endlessly!
				$res += $this->CoopForm->validate(true);
				$count++;
				
				// XXX HACK to only validate submitted subforms
				// when using server-side expanding of subforms
				$st = $this->CoopForm->getSubtables();
				foreach($st as $table => $val){
					if(strstr($val, 'Add New')){
						return $res;
					}
				}
			}



			$res += parent::validate();
			$count++;

			return $res == 2 ? true : false;
		}

	function exportValues($elementlist = null)
		{

			$res = array();
			
			//PEAR::raiseError("how did i get here?", 999);

			//confessObj($this, 'exportvalues wtf');
			
			if(count($this->CoopForm->subtables)){
				$this->CoopForm->page->confessArray($this->CoopForm->subtables, 
													"$this->CoopForm->table subbies", 
													2);
				foreach($this->CoopForm->subtables as $table => $sub){
					$res = array_merge($res, $sub->form->exportValues());
				}
			}
			
			$res = array_merge($res, parent::exportValues($elementlist));

			$this->CoopForm->page->confessArray($res, 
									  sprintf("exportValues results %s",
											  $this->CoopForm->table),
									  2);
			return $res;
		}
	



}


   ?>