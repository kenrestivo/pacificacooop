<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopMenu.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');
require_once('CoopController.php');
require_once('lib/qfc_custom.php');
require_once('HTML/QuickForm/Action.php');
require_once 'HTML/QuickForm/Action/Direct.php';


// TODO: move this to some library somewhere	 
function stripNonNumeric($val)
{
	//print "HEY [$val]";
	$res = preg_replace('/\D/', '', $val);
	//print "HO [$res]";
	return $res;
}

	 
class RSVPCode extends HTML_QuickForm_Page
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

			$this->controller->addNav(&$this);

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


class Common extends HTML_QuickForm_Page
{
	function buildForm()
		{
			$this->_formBuilt = true;




			$this->addElement('header', 'rsvpheader',
							  'Enter information from RSVP card:');
			// ticket quantity box NOTE: use "invoice" when sumbitting to paypal
			$this->addElement('text', 
							  'ticket_quantity', 
							  'Number of tickets', 
							  'size="4"');
			//confessArray($tick, 'tick');
			$this->addElement('select', 'vip_flag', "VIP?", 
							  array('No' => 'No', 
									'Yes' => 'Yes'));

			$this->setDefaults(array('payment_amount' => '$',
									 'school_year' => findSchoolYear(),
									 'ticket_type_id' => 1)); // paid for
				 

			// TODO: the ticket type box, whatever that is. see ticketwiz
			
			$this->addElement('text', 'payment_amount', 
							  'Donation amount:',
							  'size="4"');



			$this->controller->addNav(&$this);

			// XXX only for simple with no coopform! build() does it.
			//confessObj($this->controller->cp, 'cp');
			if($sid = thruAuthCore($this->controller->cp->auth)){
				$this->addElement('hidden', 'coop', $sid); 
			}

			// validation stuff

			$this->applyFilter('__ALL__', 'trim');
			$this->addRule('ticket_quantity',
							   'Must be all numbers, no letters or spaces.', 
							   'numeric', 'client');
				
			$this->applyFilter('payment_amount', 'stripNonNumeric');


		}
}

class Payment extends HTML_QuickForm_Page
{
	function buildForm()
		{
			$this->_formBuilt = true;



			$this->controller->addNav(&$this);

			// XXX only for simple with no coopform! build() does it.
			//confessObj($this->controller->cp, 'cp');
			if($sid = thruAuthCore($this->controller->cp->auth)){
				$this->addElement('hidden', 'coop', $sid); 
			}



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
			//TODO: function to get FIRST page
			$view =& $page->controller->getPage('getCode');

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


$controller =& new CoopController('RSVPs');
$controller->cp =& $cp; // DO THIS FIRST!!


$controller->addPage(new RSVPCode('rsvpcode'));
$controller->addPage(new Common ('common'));
$controller->addPage(new Payment('payment'));


// This is the action we should always define ourselves
$controller->addAction('process', new ActionProcess());

// shows the cooppage buffered headers
$controller->addAction('display', new CustomDisplay());


$controller->run();




done ();

////KEEP EVERTHANG BELOW

?>


<!-- END RSVPTEST -->


