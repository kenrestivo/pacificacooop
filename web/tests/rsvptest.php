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

			$atdf = new CoopForm(&$this->controller->cp, 'tickets', 
								 $none); // NOT the coopView above!

			$atdf->obj->fb_createSubmit = false; // important!
			$this->CoopForm =& $atdf; // ALSO IMPORTANT!
			
			$atdf->useForm(&$this);

			$atdf->obj->fb_fieldsToRender = array('income_id', 
												  'ticket_type_id',
												  // XXX temporary
												  //'lead_id',
												  //'ticket_quantity',
												  //'vip_flag'
				);

			$atdf->overrides['income']['fb_addNewLinkFields'] = array();
			
			$atdf->obj->fb_addNewLinkFields = array('income_id');

			// only here, and only so it'll be hidden
			array_push($atdf->obj->fb_requiredFields, 'lead_id', 'vip_flag');
			
			//pass thru's
			$data =& $this->controller->container();


			$atdf->obj->fb_defaults['lead_id'] = 
						$data['values']['rsvpcode']['lead_id'];

			$atdf->obj->fb_defaults['ticket_quantity'] =  
				$data['values']['common']['ticket_quantity'];
			
			$atdf->obj->fb_defaults['vip_flag'] =  
				$data['values']['common']['vip_flag'];

			$atdf->overrides['income']['fb_defaults']['payment_amount'] =  
				$data['values']['common']['payment_amount'];

			$atdf->overrides['income']['fb_addNewLinkFields'] = array();
			$atdf->overrides['income']['fb_fieldsToRender'] = 
				array('check_number', 'check_date', 'payer', 
					  'account_number', 'note', 'bookkeeper_date');
			
			$atdf->build($_REQUEST);
				 

			// XXX gah, hack around the hokey
			$this->CoopForm =& $atdf;

			$atdf->legacyPassThru();

			$atdf->addRequiredFields();

			$atdf->setDefaults();

			$this->controller->addNav(&$this);


		}

	function validate()
		{

			if(is_object($this->CoopForm)){
				// MUST send true arg to validate, or it recurses endlessly!
				$res += $this->CoopForm->validate(true);
				$count++;
				
				// XXX HACK to only validate submitted subforms
				// when using server-side expanding of subforms
				$st = $this->getSubmitValue(
					$this->CoopForm->prependTable('subtables'));
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


    function Payment($formName, $method = 'post', $target = '_self', 
					 $attributes = null)
    {
		// MUST tracksubmit, or BAAAAD things happen
        $this->HTML_QuickForm($formName, $method, '', $target, $attributes, 
							  true);
		
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
			$view =& $page->controller->getPage('rsvpcode');

			print $view->handle('display');
		}

}


///////// MAIN

//$debug = 0;

$cp = new coopPage($debug);
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

print $cp->flushBuffer();

done ();

////KEEP EVERTHANG BELOW

?>


<!-- END RSVPTEST -->


