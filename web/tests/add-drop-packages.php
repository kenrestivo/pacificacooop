<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');
require_once 'HTML/QuickForm.php';
require_once('lib/advmultselect.php');


//PEAR::setErrorHandling(PEAR_ERROR_PRINT);

function fakeProcess($vars)
{
	confessArray($vars, 'fakeprocess');
}






$debug = 2;


$cp = new coopPage( $debug);
$cp->pageTop();
//$cp->createLegacy($cp->auth);

$atd = new CoopView(&$cp, 'packages', $none);
$atd->recordActions = array('addremove' => "Add/Remove");
 

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Springfest Package Add/Remove Test</p>";

print $cp->selfURL('Refresh');

//confessObj($cp, 'cp');
$level = ACCESS_EDIT;
$p = getAuthLevel($cp->auth, 'packaging');
$admin = $p['group_level'] >= $level ? 1 : 0;
$user = $p['user_level'] >= $level ? 1 : 0;

if($admin + $user < 1){
	print "You don't have permissions to do this. Sorry.";
	done();
}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 case 'addremove':
	 $atd = new CoopForm(&$cp, 'packages', $none); // NOT the coopView above!
	 $atd->build($_REQUEST);

	 //confessObj($atd, 'atd');
	 /////////// what's included
	 $included = $atd->checkCrossLinks('auction_packages_join',
									   'auction_donation_items');

	 ///////////// the orphans to add
	 $auc = new CoopObject(&$cp, 'auction_donation_items', $none);
	 //$auc->obj->debugLevel(2);
	 $auc->obj->school_year = findSchoolYear();
	 $auc->obj->find();
	 while($auc->obj->fetch()){
		 $allpossible[$auc->obj->{$auc->pk}] =  
			 sprintf('%.42s...', 
					 implode(' - ', array($auc->obj->{$auc->pk},
										  $auc->obj->item_description)));

	 }


	 /// HEY ASSHOLE!!! THIS IS IT HIDING IN HERE!!
	 $el =& $atd->form->addElement('advmultselect', 'auction_donation_item_id', 
					   'Auction Items:', $allpossible);

	 //confessArray($included,'included');

	 $atd->form->setDefaults(array('auction_donation_item_id' =>
								   isset(
									   $_REQUEST['_qf__' . 
												 $atd->form->_attributes['name']]) ?
								   $_REQUEST['auction_donation_item_id'] :
								   $included
								 ));
							 //confessObj($atd->form, 'form');

	 // ugly assthrus
	 $atd->form->addElement('hidden', 'action', 'addremove'); 
	 $atd->form->addElement('hidden', $atd->pk, $atd->obj->{$atd->pk}); 

	 if($sid = thruAuthCore($cp->auth)){
		 $atd->form->addElement('hidden', 'coop', $sid); 
	 }
	 

	 if ($atd->form->validate()) {
		 print "saving...";
		 $atd->form->process(array(&$atd, 'processCrossLinks'));
		 // gah, now display it again. they may want to make other changes!
		 print $cp->selfURL('Look again', 
							array('action' => 'addremove',
								$atd->pk => $_REQUEST[$atd->pk]));
		 $atd->form->freeze();
	 }
	 print $atd->form->toHTML();


	 break;
		
		
 default:
	 $atd->obj->school_year = findSchoolYear();
	 print $atd->simpleTable();
	 break;
}



done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE PACKAGES -->


