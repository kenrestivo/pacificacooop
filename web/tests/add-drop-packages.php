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


PEAR::setErrorHandling(PEAR_ERROR_PRINT);

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
	 $pkg =& new CoopForm(&$cp, 'packages', &$nothing);
	 array_push($pkg->obj->fb_fieldsToRender, 
				'donated_by_text', 'bid_increment', 'starting_bid');
	 $pkg->build();

	 $atd->obj->get($_REQUEST[$atd->pk]);
	 //confessObj($atd, 'atd');
	 

	 // donated by! first guess families...
	 //$atd->obj->debugLevel(2);
	 $aifj =& new CoopObject(&$cp, 'auction_items_families_join', &$atd);
	 $aifj->obj->{$atd->pk} = $atd->obj->{$atd->pk}; // fuck joinAdd()
	 $fam =& new CoopObject(&$cp, 'families', &$aifj);
	 $fam->obj->joinAdd($aifj->obj);
	 if($fam->obj->find(true)){
		 $donatedby = $fam->obj->name . " Family";
	 }

	 // now guess companies. blah
	 $caj =& new CoopObject(&$cp, 'companies_auction_join', &$atd);
	 $caj->obj->{$atd->pk} = $atd->obj->{$atd->pk}; // fuck joinAdd()
	 $co =& new CoopObject(&$cp, 'companies', &$caj);
	 $co->obj->joinAdd($caj->obj);
	 if($co->obj->find(true)){
		 $donatedby = $co->obj->company_name;
	 }
	 

	 $pkg->form->setDefaults(array(
								 'package_type' => 'Silent',
								 'item_type' => $atd->obj->item_type,
								 'package_description' => 
								 $atd->obj->item_description,
								 'donated_by_text' => $donatedby,
								 'package_value' => $atd->obj->item_value,
								 'bid_increment' => 
								 ceil($atd->obj->item_value / 10),
								 'starting_bid' => 
								 ceil($atd->obj->item_value /2)
								 ));

	 // must pass thru!
	 $pkg->form->addElement('hidden', $atd->pk, $_REQUEST[$atd->pk]);
	 $pkg->form->addElement('hidden', 'action', $_REQUEST['action']);
	 $pkg->form->addElement('hidden', 'school_year', findSchoolYear());

	 $pkg->addRequiredFields();

	 //ok, let's DO IT
	 if ($pkg->form->validate()) {
		 print "saving...";
		 if($pkg->form->process(array(&$pkg, 'process'))){
			 //link the auctino item back to hte package!
			 $old = $atd->obj;
			 $atd->obj->package_id = $pkg->id;
			 $atd->obj->update($old);
			 print "saved!<br>";
			 print $cp->selfURL("View list");
		 }
	 } else{
		 print $pkg->form->toHTML();
	 }

	 break;
		
 case 'addtopackage':
	 //damned silly
	 $af =& new CoopForm(&$cp, 'auction_donation_items', &$nothing);
	 //$af->obj->debugLevel(2);
	 $af->obj->fb_fieldsToRender = array('package_id', 
										 'auction_donation_item_id');
	 $pkg =& new CoopForm(&$cp, 'packages', &$nothing);
 	 $af->obj->fb_preDefElements = array('package_id' => 
 										 $pkg->obj->constrainedPackagePopup());
	 $af->build($_REQUEST[$af->pk]);
	 // must pass thru!
	 $af->form->addElement('hidden', 'action', $_REQUEST['action']);

	 // the display
	 $atd->obj->{$atd->pk} = $_REQUEST[$atd->pk];
	 $atd->recordActions = false;
	 print $atd->horizTable();

	 if ($af->form->validate()) {
		 print "saving...";
		 if($af->form->process(array(&$af, 'process'))){
			 print "saved!<br>";
		 }
	 } else {
		 print $af->form->toHTML();
	 }
	 break;

 case 'details':

	 break;
		
 default:
	 $atd->obj->school_year = findSchoolYear();
	 $atd->obj->fb_fieldsToRender = array('item_description', 'quantity', 
										  'item_value', 
										  'item_type');
	 $atd->obj->whereAdd('date_received > "2000-01-01"');
	 $atd->obj->whereAdd('package_id < 1');
	 print $atd->simpleTable();
	 break;
}



done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE PACKAGES -->


