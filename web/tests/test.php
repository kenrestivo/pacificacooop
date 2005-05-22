<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('first.inc');
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');




//$debug = 2;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();

print $cp->selfURL('refresh (for testing)');

$fam =& new CoopForm(&$cp, 'families', &$nothing);
$fam->build();
$fam->legacyPassThru();
$fam->addRequiredFields();
print $fam->form->toHTML();

$par1 =& new CoopForm(&$cp, 'parents', &$fam);
$par1->build();
$par1->legacyPassThru();
$par1->addRequiredFields();
print $par1->form->toHTML();

$par2 =& new CoopForm(&$cp, 'parents', &$fam);
$par2->build();
$par2->legacyPassThru();
$par2->addRequiredFields();
print $par2->form->toHTML();

$kid =& new CoopForm(&$cp, 'kids', &$fam);
$kid->build();
$kid->legacyPassThru();
$kid->addRequiredFields();
print $kid->form->toHTML();

$enrol =& new CoopForm(&$cp, 'enrollment', &$kid);
$enrol->build();
$enrol->legacyPassThru();
$enrol->addRequiredFields();
print $enrol->form->toHTML();

done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SANE-DONATE  -->


