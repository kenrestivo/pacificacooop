<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('ThankYou.php');





PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$debug = 0;


//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();

$atd = new CoopView(&$cp, 'users', $none);
$atd->permissions = array('reset' => 'Reset Password');


print $cp->selfURL('Refresh');

$ty = new ThankYou(&$cp);
$ty->repairOrphaned();



done ();

////KEEP EVERTHANG BELOW

?>


<!-- END TEST -->


