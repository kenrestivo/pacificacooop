<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('first.inc');
require_once('CoopPage.php');
require_once('RSVPWizard.php');


PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$debug = 0;


$cp = new coopPage( $debug);

// make it leagal html, and test it
$cp->header();



print $cp->selfURL('Refresh');


$wiz =& new RSVPWizard(&$cp);
$wiz->run();

$_SESSION=$_REQUEST; 			// keep sessioninit happy

done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SANE-DONATE  -->


