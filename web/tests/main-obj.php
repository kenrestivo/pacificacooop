<?php 

//$Id$

require_once('CoopPage.php');
require_once('CoopMenu.php');

$debug = 1;

//MAIN
//$_SESSION['toptable'] 



$cp =& new coopPage(1);
$cp->pageTop();
$menu =& new CoopMenu;
$menu->create(&$cp);
print $menu->topNavigation();
print $menu->toHTML();
$cp->engine();

done();


////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->


