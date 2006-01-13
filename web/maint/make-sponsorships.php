<?php

//$Id$
/*
        this should NOT be necessary anymore! 
        i now have a pseudo-listener-type pattern going on
        where whenever anyone adds anything pertaining to sponsorships,
        it'll recalculate them

*/


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

$atd = new CoopView(&$cp, 'sponsorships', $none);

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Springfest Sponsors Needed</p>";


print $cp->selfURL(array('value' =>'View Sponsorships'));
 print $cp->selfURL(array('value' => 'Find Needed', 
                    'inside' => array('action' => 'findneeded')));
 print $cp->selfURL(array('value' => 'Add Needed', 
                    'inside' => array('action' => 'addneeded')));



$sy = $cp->currentSchoolYear;


//TODO: merge this with sponsorships page?
//		or, is this a one-off i'll never need again?


// TODO put this back after i push it live
// if($admin + $user < 1){
//  	print "You don't have permissions to do this. Sorry.";
//  	done();
// }


function viewHack(&$cp, &$atd, $sy)
{
	 $co =& new CoopObject(&$cp, 'sponsorship_types', &$atd);
     $atd->protectedJoin($co);
	 $atd->obj->orderBy('sponsorship_price desc');
	 return $atd->simpleTable();
			
}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
//// FIND NEEDED /////
 case 'findneeded':
	 print "<p>This could take a while. Calculating sponsors needed.</p>";
	 $sp = new Sponsorship(&$cp, $sy);
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
                 $tp =& $co->factory('sponsorship_types');
                 $tp->get($type);
				 printf("(%s)%s %s %s => %d<br>", $tab, $co->obj->company_name, 
						$co->obj->first_name, 
						$co->obj->last_name, 
						$tp->sponsorship_name);
			 }
		 }
	 }
	 print "<p>The above need to have sponsorships added.</p>";
	 break;

//// ADD NEEDED /////////
 case 'addneeded':
	 print "<p>This could take a very, very, very long time. Please be patient.</p>";
	 $sp = new Sponsorship(&$cp, $sy);
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
                 $tp =& $co->factory('sponsorship_types');
                 $tp->get($type);
				 printf("(%s)%s %s %s => %d<br>", $tab, $co->obj->company_name, 
						$co->obj->first_name, 
						$co->obj->last_name, 
						$tp->sponsorship_name);
			 }
		 }
	 }
	 print "<p><b>These have been added to the db, or updated!</b></p>";
	 break;
	

 case 'details':
	 
	 break;
	 

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp, &$atd, $sy);

	 break;
}




done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE SPONSORSHIPS -->


