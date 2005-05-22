<?php

   // $Id$

require_once 'HTML/QuickForm/Action/Jump.php';
require_once 'HTML/QuickForm/Action/Display.php';

class JumpDisplay  extends HTML_QuickForm_Action_Jump
{
	function perform(&$page, $actionName)
		{
			// ok, this is a cut-and-paste from action_jump,
			// but does display not location

			//$this->controller->cp->debug > 1 && 
			print "DEBUG jump [$actionName] for " . $page->getAttribute('id');

			//confessObj($page, 'the page receiving the jump');

			// check whether the page is valid before trying to go to it
			if ($page->controller->isModal()) {
				// we check whether *all* pages up to current are valid
				// if there is an invalid page we go to it, instead of the
				// requested one
				$pageName = $page->getAttribute('id');
				if (!$page->controller->isValid($pageName)) {
					$pageName = $page->controller->findInvalid();
				}
				$current =& $page->controller->getPage($pageName);

			} else {
				$current =& $page;
			}

			// stupid desperate hacks that don't fix anything
			//$data     =& $page->controller->container();
			//$current->_formBuilt = false;
			
 			// ok, now call display on  $current!
			return $current->handle('display');
		}

}

class CustomDisplay extends HTML_QuickForm_Action_Display
{
	function _renderForm(&$page)
		{
			global  $hack;
			print $hack;
			print parent::_renderForm($page);

		}



}



   ?>