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
require_once('Sponsorship.php');


function testsave($vars){
	confessArray($vars, 'vars');

	return true;
}

PEAR::setErrorHandling(PEAR_ERROR_PRINT);


//$debug = 2;

//MAIN


//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();
//$cp->createLegacy($cp->auth);

$atd = new CoopView(&$cp, 'sponsorships', $none);
$atd->recordActions = array('edit' => "Edit",
							'confirmdelete' => 'Delete');

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Springfest Sponsors Needed</p>";

print $cp->selfURL('View Sponsorships');
print $cp->selfURL('Find Needed', array('action' => 'findneeded'));
print $cp->selfURL('Add Needed', array('action' => 'addneeded'));

//confessObj($cp, 'cp');
$level = ACCESS_EDIT;
$p = getAuthLevel($cp->auth, 'solicitation');
$admin = $p['group_level'] >= $level ? 1 : 0;
$user = $p['user_level'] >= $level ? 1 : 0;

// if($admin + $user < 1){
// 	print "You don't have permissions to do this. Sorry.";
// 	done();
// }


function viewHack(&$cp, &$atd)
{
	 $co =& new CoopObject(&$cp, 'sponsorship_types', &$atd);
	 $atd->obj->joinAdd($co->obj);
	 $atd->school_year = findSchoolYear();
	 $atd->obj->orderBy('sponsorship_price desc');
	 return $atd->simpleTable();
			
}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
//// FIND NEEDED /////
 case 'findneeded':
	 print "<p>This could take a while. Calculating sponsors needed.</p>";
	 $sp = new Sponsorship(&$cp);
	 foreach(array('companies', 'leads') as $tab){
		 //print $tab;
		 $co =& new CoopObject(&$cp, $tab, &$nothing);
		 $co->obj->orderBy($tab == 'leads' ? 'company' : 'company_name', //HACK
						   'last_name', 'first_name');
		 //$co->obj->debugLevel(2);
		 $co->obj->find();
		 while($co->obj->fetch()){
			 if($type = $sp->calculateSponsorshipType($co->obj->{$co->pk}, 
													  $co->pk)){
				 printf("(%s)%s %s %s => %d<br>", $tab, $co->obj->company_name, 
						$co->obj->first_name, 
						$co->obj->last_name, 
						$type);
			 }
		 }
	 }
	 print "<p>The above need to have sponsorships added.</p>";
	 break;

//// ADD NEEDED /////////
 case 'addneeded':
	 print "<p>This could take a very, very, very long time. Please be patient.</p>";
	 $sp = new Sponsorship(&$cp);
	 foreach(array('companies', 'leads') as $tab){
		 //print $tab;
		 $co =& new CoopObject(&$cp, $tab, &$nothing);
		 $co->obj->orderBy($tab == 'leads' ? 'company' : 'company_name', //HACK
						   'last_name', 'first_name');
		 //$co->obj->debugLevel(2);
		 $co->obj->find();
		 while($co->obj->fetch()){
			 if($type = $sp->updateSponsorships($co->obj->{$co->pk}, 
													  $co->pk)){
				 printf("(%s)%s %s %s => %d<br>", $tab, $co->obj->company_name, 
						$co->obj->first_name, 
						$co->obj->last_name, 
						$type);
			 }
		 }
	 }
	 print "<p><b>These have been added to the db, or updated!</b></p>";
	 break;
	

 case 'details':
	 
	 break;
	 
//// EDIT //////
 case 'edit':
	 print "<p>Choose either a company name or invitee name, not both. If you picked the wrong one by mistake, change the other one to 'CHOOSE ONE'.</p>";

	 $atdf = new CoopForm(&$cp, 'sponsorships', $none); // NOT the coopView above!

	 $atdf->obj->fb_fieldsToRender = array('company_id', 'lead_id', 
										   'sponsorship_type_id', 
										   'entry_type');
	 $atdf->build($_REQUEST);


	 // ugly assthrus for my cheap dispatcher
	 $atdf->form->addElement('hidden', 'action', 'edit'); 

	 $atdf->legacyPassThru();

	 $atdf->addRequiredFields();
	 
	 // tweak them all to be manual now!
	 $el =& $atdf->form->getElement('entry_type');
	 $el->setValue('Manual');

	 if ($atdf->form->validate()) {
		 print "saving...";
		 print $atdf->form->process(array(&$atdf, 'process'));
		 // gah, now display it again. they may want to make other changes!
		 print viewHack(&$cp, &$atd);
	 } else {
		 print $atdf->form->toHTML();
	 }
	 break;

 case 'confirmdelete':
	 print "<p>Are you sure you wish to delete this? Click 'Delete' below to delete it, or the 'Back' button in your broswer to cancel.</p>";	 $atdf = new CoopForm(&$cp, 'sponsorships', $none); // NOT the coopView above!
	 $atdf->build($_REQUEST);

	 // ugly assthrus for my cheap dispatcher
	 $atdf->form->addElement('hidden', 'action', 'edit'); 

	 $atdf->legacyPassThru();

	 $atdf->addRequiredFields();


	 // change the save button and action to delete
 	 $el =& $atdf->form->getElement('savebutton');
 	 $el->setValue('Delete');
 	 $el =& $atdf->form->getElement('action');
 	 $el->setValue('delete');
	 
	 //TODO and add a cancel button
	 //$atdf->form->addElement('button', 'cancelbutton', 'Cancel');

	 $atdf->form->freeze();

	 print $atdf->form->toHTML();

	 break;

//// DELETE ////
 case 'delete':
 // hack , but it works. why reinvent the wheel?
	 $atdf = new CoopForm(&$cp, 'sponsorships', $none); // NOT the coopView above!
	 $atdf->build($_REQUEST);
	 $atdf->obj->delete();
	 print viewHack(&$cp, &$atd);

	 break;


//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp, &$atd);

	 break;
}




done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE PACKAGES -->


