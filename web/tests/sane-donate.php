<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('paypal.php');



PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$debug = 1;

function donateDispatcher(&$cp, $action = false)
{
// there are other actions but thay happen in IPN and postpaypal
	$actionsequence = array('getcode', 
							'verifyuser',
							'newrsvp',
							'confirmrsvp');

	switch($action){
	case 'getcode':
		$form =& new HTML_QuickForm( 'Springfest RSVP', 'getcodeform');
		$form->addElement('text', 'response_code', 
						  'Please enter your Response Code here:', 
						  'size="4"');
		$form->addElement('submit', 'verifyuser', 'Next>>');
		
		// important
		if(SID){
			$form->addElement('hidden', 'coop', session_id()); 
		}
		
		$form->applyFilter('__ALL__', 'trim');
		$form->addRule('response_code', 
					   'Response code is required.', 
					   'required', 'client');
		$form->addRule('response_code', 
					   'Response codes are all numbers, no letters or spaces.', 
					   'numeric', 'client');

		// add custom rule: check db!
		if($form->validate()){
			donateDispatcher(&$cp, 'verifyuser');
		} else {
			print $form->toHTML();
		}
		break;
		
	case 'verifyuser':
		$top = new CoopView(&$cp, 'leads', &$nothing);
		$mi = $top->pk;
		$top->obj->$mi = $_REQUEST['response_code'];;
		$found = $top->obj->find(true);
		if(!$found){
			printf("<p>I'm sorry, there is no such code as %d. You may have mistyped or the code on your invitation may not be legible.</p>",
				   $_REQUEST['response_code']);
			print $cp->selfURL("Please try again.", 
							   array($_REQUEST['response_code'],
									 'action' => 'getcode'));
			break;
		}
		// ripped from thankyou, mostly. should abstract it out!
		$address_array[] = implode(' ', array($top->obj->salutation,
										   $top->obj->first_name,
										   $top->obj->last_name
									));

		foreach(array('title', 'company_name', 'address1', 'address2') 
				as $var)
		{
			if($top->obj->$var){
				$address_array[] = $top->obj->$var;
			}
		}
		$address_array[] = sprintf("%s %s, %s", 
										 $top->obj->city,
										 $top->obj->state,
										 $top->obj->zip);		
		$address = implode('<br>', $address_array);
		print "<h3>Is this you?</h3><p>$address</p>";
		//print $top->horizTable();
		print $cp->selfURL("Yes, that's me!", 
						   array('response_code' =>
								 $_REQUEST['response_code'],
								 'action' => 'newrsvp'));
		print $cp->selfURL("No, that's not me.", 
						   array($_REQUEST['response_code'],
								 'action' => 'getcode'));
		break;
		
	case 'newrsvp':
		//NOTE whatever calls this must set "leadid"
		$ppf =& new PayPalForm();
		$form =& $ppf->buildRSVP(&$cp);
		
		print $form->toHTML();
		break;
		
	case 'confirmrsvp':
		// XXX will i use process() here instead?
		// something must set SOURCE
		break;
		
	default:
		donateDispatcher(&$cp, 'getcode');
		break;
	}
} // end donatedispatcher


$cp = new coopPage( $debug);

// make it leagal html, and test it
$cp->header();

$_SESSION=$_REQUEST; 			// keep sessioninit happy

print $cp->selfURL('Refresh');


donateDispatcher(&$cp, $_REQUEST['action']);

done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SANE-DONATE  -->


