<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
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

	 
$ppf =& new PayPalForm();
$form =& $ppf->buildRSVP(&$cp);

print $form->toHTML();


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SANE-DONATE  -->


