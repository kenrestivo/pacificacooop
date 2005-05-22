<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopMenu.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');
require_once('lib/qfc_custom.php');
require_once('HTML/QuickForm/Controller.php');
require_once('HTML/QuickForm/Action.php');
require_once 'HTML/QuickForm/Action/Direct.php';

	 

	 
class GetCode extends HTML_QuickForm_Page
{

	function buildForm()
		{
			$this->_formBuilt = true;

			$this->addElement('text', 'lead_id', 
							  'Please enter the Response Code here:', 
							  'size="4"');


			// XXX only for simple with no coopform! build does it.
			//confessObj($this->controller->cp, 'cp');
			if($sid = thruAuthCore($this->controller->cp->auth)){
				$this->addElement('hidden', 'coop', $sid); 
			}

			$this->setDefaultAction('next');

			 $this->addElement('submit',   
							   $this->getButtonName('next'), 'Next >>');

			 //validation stuff
			$this->applyFilter('__ALL__', 'trim');
			$this->addRule('lead_id', 
						   'Response code is required.', 
						   'required', 'client');
			$this->addRule('lead_id', 
						   'Response codes are all numbers, no letters or spaces.', 
						   'numeric', 'client');
	
			// add custom rule: check db!
			$this->registerRule('checkcode', 'callback', 'checkcode', &$this);
			$this->addRule('lead_id', 
						   "I'm sorry, there is no such code. You may have mistyped or the code on your invitation may not be legible.", 
						   'checkcode');

		}

	// make sure it's a valid code
	function checkCode($responsecode)
		{
			
			$lead =& new CoopObject(&$this->controller->cp, 'leads', &$nothing);
			$found = $lead->obj->get($responsecode);
			return $found > 0 ? true : false;
		}
	
	
} // END GETCODE CLASS


class GetBranchData extends HTML_QuickForm_Page
{
	function buildForm()
		{
			$this->_formBuilt = true;
				 

			$nav[] =& $this->createElement(
				'submit',   $this->getButtonName('back'), '<< Back');
			$nav[] =& $this->createElement(
				'submit',   $this->getButtonName('next'), 'Finish');
			$this->addGroup($nav, null, '', '&nbsp;', false);
				 
			// XXX only for simple with no coopform! build() does it.
			//confessObj($this->controller->cp, 'cp');
			if($sid = thruAuthCore($this->controller->cp->auth)){
				$this->addElement('hidden', 'coop', $sid); 
			}

			$this->setDefaultAction('next');
		}
}



class ActionProcess extends HTML_QuickForm_Action
{
	function perform(&$page, $actionName)
		{
			print $page->controller->cp->flushBuffer();
			echo "Submit successful!<br>\n<pre>\n";
			confessObj($page->controller, 'yay');
			echo "\n</pre>\n";
				 
			//clean up after yourself, and bring me back to top!
			$page->controller->container(true);
			$view =& $page->controller->getPage('view');

			print $view->handle('display');
		}

}


///////// MAIN

$cp = new coopPage( $debug);
$cp->buffer($cp->pageTop());


$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
$cp->buffer($menu->topNavigation());

$cp->buffer("<p>RSVPs</p>");


$controller =& new HTML_QuickForm_Controller('RSVPs');
$controller->cp =& $cp; // DO THIS FIRST!!


$controller->addPage(new GetCode('getCode'));
$controller->addPage(new GetBranchData('getBranchData'));


// This is the action we should always define ourselves
$controller->addAction('process', new ActionProcess());

// shows the cooppage buffered headers
$controller->addAction('display', new CustomDisplay());


$controller->run();




done ();

////KEEP EVERTHANG BELOW

?>


<!-- END RSVPTEST -->


