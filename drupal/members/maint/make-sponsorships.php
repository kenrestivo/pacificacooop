<?php


//  Copyright (C) 2004-2006  ken restivo <ken@restivo.org>
// 
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
// 
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details. 
// 
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


// $Id$
/*
        this should NOT be necessary anymore! 
        i now have a pseudo-listener-type pattern going on
        where whenever anyone adds anything pertaining to sponsorships,
        it'll recalculate them

*/


require_once('../includes/first.inc');
require_once('COOP/Page.php');
require_once('COOP/View.php');
require_once('COOP/Form.php');
require_once('COOP/Menu.php');
require_once('HTML/Table.php');
require_once 'HTML/QuickForm.php';
require_once('DB/DataObject/Cast.php');
require_once('COOP/Sponsorship.php');


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
	 $co =& new CoopView(&$cp, 'sponsorships', &$atd);
	 return $co->simpleTable();
			
}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
//// FIND NEEDED /////
 case 'findneeded':
	 print "<p>This could take a while. Calculating sponsors needed.</p>";
//      ob_end_flush();
//      flush();
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
                 $tp =& $co->obj->factory('sponsorship_types');
                 $tp->get($type);
				 printf("(%s)%s %s %s => %s<br>", $tab, $co->obj->company_name, 
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
//      ob_end_flush();
//      flush();
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
                 $tp =& $co->obj->factory('sponsorship_types');
                 $tp->get($type);
				 printf("(%s)%s %s %s => %s<br>", $tab, $co->obj->company_name, 
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


