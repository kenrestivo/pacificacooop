<?php 

//$Id$
require_once("CoopPage.php");
require_once("CoopMenu.php");

///////////////////////
$cp = new CoopPage;
$cp->pageTop();

$menu =& new CoopMenu;
$menu->createLegacy(&$cp);
confessObj($menu, "menuonb");
print $menu->toHTML();


	
$menu->forceCurrentURL('10names.php');
$menu->setMenuType('urhere');
$menu->show();




done();





////KEEP EVERTHANG BELOW

?>
<!-- END TEST -->


