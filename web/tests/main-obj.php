<?php 

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once("utils.inc");
require_once('CoopPage.php');
require_once('CoopView.php');

$debug = 1;

//MAIN
//$_SESSION['toptable'] 



$cp = new coopPage( $debug);
$cp->pageTop();



$view =& new CoopView(&$cp, 'companies');
print $view->recurseTable();

done ();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->


