<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopObject.php');
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

// ticket quantity box NOTE: use "invoice" when sumbitting to paypal
$form->addElement('text', 'ticket_quantity', 'Number of tickets:', 
				  'size="4"');

//popup for sponsor levels: grab from dbdo
$stypes['none'] = '-- CHOOSE ONE --';
$spon =& new CoopObject(&$cp, 'sponsorship_types', &$nothing);
$spon->obj->school_year = '2004-2005';
$spon->obj->orderBy('sponsorship_price desc');
$spon->obj->find();
while($spon->obj->fetch()){
	$stypes[$spon->obj->sponsorship_price] = 
		sprintf('%s ($%.0f)', $spon->obj->sponsorship_name,
				$spon->obj->sponsorship_price);
}
$stypes['other'] = 'Other...';
//confessArray($stypes, 'stypes');

$combo[] =& HTML_QuickForm::createElement('select', 
										'sponsor_amount', 
										'Sponsorship Level', 
										$stypes);

// add Other... and dynamically add OTHER box based on its presence
$combo[] =& HTML_QuickForm::createElement('text', 'other_amount', 
										  'Other Amount:',
										  'size="4"');

$form->addGroup($combo, 'combo', 'Donate:', '&nbsp;Other Amount: ');


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


