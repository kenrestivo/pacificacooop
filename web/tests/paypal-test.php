<?php 

//$Id$
require_once("CoopPage.php");
require_once("CoopMenu.php");

require_once("everything.inc");
require_once("members.inc");

PEAR::setErrorHandling(PEAR_ERROR_PRINT);


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


