<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('HTML/QuickForm.php');
require_once('paypal.php');



PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$debug = 0;

// TODO: make it leagal html, and test it


//DB_DataObject::debugLevel(2);
confessArray($_REQUEST, 'request');

$cp = new coopPage( $debug);
$_SESSION=$_REQUEST; 			// keep sessioninit happy

print $cp->selfURL('Refresh');

//NOTE whatever calls this must set "source"


$form = new HTML_QuickForm( 'Springfest RSVP', 'rsvpform');

// ticket quantity box
$form->addElement('text', 'ticket_quantity', 'Number of tickets: ');

//popup for sponsor levels: grab from dbdo
$form->addElement('select', 'sponsorship_type', 'Donation Amount', &$nothing);

// add Other... and dynamically add OTHER box based on its presence
$form->addElement('text', 'other_amount', 'Other');

// a frozen TOTAL DONATION box too, before they paypal in
$form->addElement('submit', 'verify', 'Next>>');

// important
if(SID){
	$form->addElement('hidden', 'coop', session_id()); 
}
	 
//TODO pass through ANY OTHER VARS!
// i.e. the lead id, weirdo paypal.php vars, etc

print $form->toHTML();


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SANE-DONATE  -->


