<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('first.inc');
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('TicketWizard.php');



PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'confessObj');


$debug = 2;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();

print $cp->selfURL('refresh (for testing)');

$tick = new TicketWizard(&$cp);
$tick->run('genericentry');
							






done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SANE-DONATE  -->


