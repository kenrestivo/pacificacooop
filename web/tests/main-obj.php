<?php 

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once("utils.inc");
require_once('CoopPage.php');
require_once('CoopMenu.php');

$debug = 1;

//MAIN
//$_SESSION['toptable'] 



$cp =& new coopPage($debug);
$cp->pageTop();

done();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->


