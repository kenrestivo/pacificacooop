<?php 

//$Id$
require_once("CoopPage.php");
require_once("CoopMenu.php");

///////////////////////
$cp = new CoopPage;
$cp->pageTop();

$menu =& new CoopMenu;
$menu->createLegacy(&$cp);
print $menu->topNavigation();

//confessObj($menu, "menuonb");
print $menu->toHTML();


	
$menu->forceCurrentURL('10names.php');
print $menu->get('urhere');




done();





////KEEP EVERTHANG BELOW

?>
<!-- END TEST -->


