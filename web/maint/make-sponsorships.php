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
print $cp->pageTop();
//$cp->createLegacy($cp->auth);

$atd = new CoopView(&$cp, 'sponsorships', $none);

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Springfest Sponsors Needed</p>";

print $cp->selfURL('View Sponsorships');
 print $cp->selfURL('Find Needed', array('action' => 'findneeded'));
 print $cp->selfURL('Add Needed', array('action' => 'addneeded'));

if(!checkAuthLevel($cp->auth, 0, 'solicit_money', ACCESS_EDIT, 
				   $cp->userStruct)){
 	print "You don't have permissions to do this. Sorry.";
 	done();
}

// TODO put this back after i push it live
// if($admin + $user < 1){
//  	print "You don't have permissions to do this. Sorry.";
//  	done();
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
	 

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp, &$atd);

	 break;
}




done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE SPONSORSHIPS -->


