<?php 

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopMenu.php');

$debug = 1;

//MAIN
//$_SESSION['toptable'] 



$cp =& new coopPage(1);
$cp->pageTop();
$menu =& new CoopMenu;


$menu->page =& $cp;
$menu->setMenu(
	array(
		array(
			'title' => 'Solicitation Test',
			'url' => $cp->selfURL(
				false, 'tables[companies][action]=list')),
		array(
			'title' => 'New Invitations Test',
			'url' => $cp->selfURL(
				false, 'tables[invitations][action]=list')),
		array(
			'title' => 'Files Test',
			'url' => $cp->selfURL(
				false, 'tables[files][action]=list')),
		array(
			'title' => 'Leads Test',
			'url' => $cp->selfURL(
				false, 'tables[leads][action]=list'))));



print $menu->topNavigation();
print $menu->kenRender();
$cp->engine();

//global $_DB_DATAOBJECT;
//confessObj($_DB_DATAOBJECT, "dataobject");

done();


////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->


