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



   ?>