<?php 

//$Id$
require_once("CoopPage.php");

require_once 'HTML/Menu.php';
require_once 'HTML/Menu/DirectTreeRenderer.php';


function indexEverything($everything)
{
	foreach ($everything as $thang => $val){
		$val['fields'] = $$val['fields'];
		$indexed_everything[$val['page']] = $val;
	
	}
	//confessArray($indexed_everything, 'indexedeverythinag');
	return $indexed_everything;
} 

function callbacksToMenu($everything)
{
	foreach($everything as $key => $cbs){
		$menustruct[] = array(
			'title' => $cbs['description'],
			'url' => $cbs['page']);
	}
	//confessArray($menustruct, 'menustruct');
	return $menustruct; 
}

function getMoney($ie)
{
	foreach($ie as $page => $cbs){
		if($cbs['maintable'] == 'income'){
			$moneymenu[] = array(
				'title' => $cbs['description'],
				'url' => $cbs['page']);
		}
		confessArray($moneymenu, 'moneymenu');
		return $moneymenu;
	}
}

function getRealms($ie)
{
	foreach($ie as $key => $cbs){
		$realms[] = substr($cbs['realm'], 0, 7);
				
	}
	$realms = array_unique($realms);
	asort($realms);
confessArray($realms, "realmsort");
	return $realms;
}

function nestByRealm($ie, $realms)
{
	foreach($realms as $realm => $description){
		$res[$realm]['title'] = $description;
		foreach($ie as $key => $cbs){
			if(strncmp($cbs['realm'], $realm, 7) == 0){
				$res[$realm]['sub'][$key] = array(
					'title' => $cbs['description'],
					'url' => $cbs['page']);
				}
		}
	}
	return $res;
}

///////////////////////
$cp = new CoopPage;
$cp->pageTop();

include('everything.inc');
$sf = indexEverything($everything);
include('members.inc');
$members = indexEverything($everything);


$realmmap = array( 
	'auction' => 'Auctions',
	'flyers' => 'Flyers',
	'invitations' => 'Invitations',
	'money' => 'Family Fees',
	'nag' => 'Reminders',
	'packaging' => 'Packaging',
	'raffle' => 'Raffles',
	'solicit' => 'Solicitation',
	'tickets' => 'Tickets'

);



$heirmenu = array(
	array(
		'title' => 'Enhancement',
		'sub' => callbacksToMenu($members)),
	array(
		'title' => 'Springfest',
		'sub' => nestByRealm($sf, $realmmap)));
	
//confessArray($sf, "sf");

$menu =& new HTML_Menu($heirmenu, "sitemap");
$renderer =& new HTML_Menu_DirectTreeRenderer();
$menu->render($renderer, $type);
print $renderer->toHtml();

confessArray(nestByRealm($sf, getRealms($sf)), "nestedbyrealm");
	
$menu->forceCurrentURL('10names.php');
$menu->setMenuType('urhere');
$menu->show();



done();





////KEEP EVERTHANG BELOW

?>
<!-- END TEST -->


