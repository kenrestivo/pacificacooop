<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');
require_once('HTML/Table.php');
require_once 'HTML/QuickForm.php';
require_once('DB/DataObject/Cast.php');


//PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$debug = 2;


$cp = new coopPage( $debug);
$cp->pageTop();
//$cp->createLegacy($cp->auth);

$atd = new CoopView(&$cp, 'packages', $none);
$atd->recordActions = array('edit' => "Edit");
 

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
 case 'edit':
	 $atd->obj->get($_REQUEST[$atd->pk]);
	 print $atd->horizTable();

	 $form =& new HTML_QuickForm('auctionchooser');

	 // what's included

	 $auc = new CoopObject(&$cp, 'auction_donation_items', $none);
	 //$auc->obj->debugLevel(2);
	 $apj =& new CoopObject(&$cp, 'auction_packages_join', &$auc);
	 $apj->obj->{$atd->pk} = $atd->obj->{$atd->pk};
	 $auc->obj->joinAdd($apj->obj);
	 $auc->obj->orderBy($auc->pk);
	 $found = $auc->obj->find();
	 while($auc->obj->fetch()){
		 $included[$auc->obj->{$auc->pk}] =  
			 sprintf('%.42s...', 
					 implode(' - ', array($auc->obj->{$auc->pk},
										  $auc->obj->item_description)));

	 }
	 if(!$found){
		 $included[] = "No Auction items in this package!";
	 }
	 $sel =& $form->addElement('select', 'included_auction_items', 
					   'Includes:', &$included, array('size' => 10,
													  'width' => 42));
	 $sel->setMultiple(true);


	 // the orphans to add
	 $auc = new CoopObject(&$cp, 'auction_donation_items', $none);
	 $auc->obj->whereAdd(sprintf('school_year = "%s"', findSchoolYear()));
	 $auc->obj->orderBy($auc->pk);
	 $auc->obj->find();
	 while($auc->obj->fetch()){
		 $options[$auc->obj->{$auc->pk}] =  
			 sprintf('%.42s...', 
					 implode(' - ', array($auc->obj->{$auc->pk},
										  $auc->obj->item_description)));

	 }

	 $sel =& $form->addElement('select', 'orphaned_auction_items', 
					   'Orphaned Auctions', &$options, array('size' => 10));
	 $sel->setMultiple(true);

	 if($sid = thruAuthCore($cp->auth)){
		 $form->addElement('hidden', 'coop', $sid); 
	 }
	 
	 $form->addElement('submit', null, '<<Add');
	 $form->addElement('submit', null, 'Remove>>');



	 $sel->setMultiple(true);

 
	 print $form->toHTML();

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


