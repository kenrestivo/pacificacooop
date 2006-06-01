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

print $cp->selfURL(array('value' =>'refresh (for testing)'));

confessArray(get_browser());


$fam =& new CoopObject(&$cp, 'leads', &$nothing);
$fam->obj->query('select * from leads where lead_id = 333');
confessObj($fam->obj, 'famobj');

done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SANE-DONATE  -->


