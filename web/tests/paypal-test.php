<?php 

//$Id$
require_once("first.inc");
require_once("shared.inc");
require_once("auth.inc");

require_once 'HTML/Menu.php';
require_once 'HTML/Menu/DirectTreeRenderer.php';


function indexEverything($everything){
	foreach ($everything as $thang => $val){
		$val['fields'] = $$val['fields'];
		$indexed_everything[$val['page']] = $val;
	
	}
	//confessArray($indexed_everything, 'indexedeverythinag');
	return $indexed_everything;
} 

function callbacksToMenu($everything){
	foreach($everything as $key => $cbs){
		$menustruct[] = array(
			'title' => $cbs['description'],
			'url' => $cbs['page']);
	}
	//confessArray($menustruct, 'menustruct');
	return $menustruct; 
}

function getMoney($ie){
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


///////////////////////
print '<HTML>
				<HEAD>
						<link rel=stylesheet href="main.css" title=main>
							<TITLE>Data Entry</TITLE>
				</HEAD>
				<BODY>
				<h2>Pacifica Co-Op Nursery School Data Entry</h2>
				';

			
///
warnDev();

user_error("states.inc: ------- NEW PAGE --------", E_USER_NOTICE);


$this->auth = logIn($_REQUEST);


if($this->auth['state'] != 'loggedin'){
	done();
}


topNavigation($this->auth,  getUser($this->auth['uid']));



include('everything.inc');
$sf = indexEverything($everything);
include('members.inc');
$members = indexEverything($everything);


$heirmenu = array(
	array(
		'title' => 'Enhancement',
		'sub' => callbacksToMenu($members)),
	array(
		'title' => 'Springfest',
		'sub' => callbacksToMenu($sf)));
	
//confessArray($sf, "sf");

$menu =& new HTML_Menu($heirmenu, "sitemap");
$renderer =& new HTML_Menu_DirectTreeRenderer();
$menu->render($renderer, $type);
print $renderer->toHtml();


	
//$menu->setMenuType('urhere');
//$menu->show();



done();





////KEEP EVERTHANG BELOW

?>
<!-- END TEST -->


