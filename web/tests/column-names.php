<?php 

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once("utils.inc");
require_once("members.inc");
require_once("everything.inc");
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopMenu.php');

$debug = 0;

//MAIN
//$_SESSION['toptable'] 

$cp =& new CoopPage(1);
$cp->pageTop();

$menu =& new CoopMenu;
$menu->createLegacy(&$cp);

print $menu->kenRender();

$cp->confessArray($menu->indexed_all, "indexedall");

done ();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->





