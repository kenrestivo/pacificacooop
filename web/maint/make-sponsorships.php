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

if($admin + $user < 1){
	print "You don't have permissions to do this. Sorry.";
	done();
}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 case 'findneeded':
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
	 break;

 case 'addneeded':
	 print "<p><b>These have been added to the db, or updated!</b></p>";
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
	 break;
	

 case 'details':
	 
	 break;
	 
 default:

	 break;
}




done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE PACKAGES -->


