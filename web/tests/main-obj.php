<?php 

//$Id$

require_once('CoopPage.php');
require_once('CoopMenu.php');

//MAIN
//$_SESSION['toptable'] 

function testMenu(&$page, &$menu)
{		
	$menu->page =& $page;
 
	print "HEY" .  $page->selfURL(false, 'companies[action]=list');
	$heirmenu = array(
		array(
			'title' => 'Solicitation Test',
			'url' => $page->selfURL(false, 'companies[action]=list')),
		array(
			'title' => 'Invitations Test',
			'url' => $page->selfURL(false, 
								  'invitations[action]=list')));


	$menu->setMenu($heirmenu);

	$menu->renderer =& new HTML_Menu_DirectTreeRenderer();
	$menu->render($menu->renderer, 'sitemap');
}



$cp =& new coopPage;
$cp->pageTop();
$menu =& new CoopMenu;
testMenu(&$cp, &$menu);
print $menu->topNavigation();
print $menu->toHTML();
$cp->engine();

done();


////KEEP EVERTHANG BELOW

?>
<!-- END COOP PAGE -->


