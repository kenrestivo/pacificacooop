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


function testsave($vars){
	confessArray($vars, 'vars');

	return true;
}

PEAR::setErrorHandling(PEAR_ERROR_PRINT);




//$debug = 2;

//MAIN
//$_SESSION['toptable'] i

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();

$atd = new CoopView(&$cp, 'tickets', $none);
$sy=findSchoolYear();
 

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Springfest Family Tickets</p>";


//confessObj($cp, 'cp');
$level = ACCESS_EDIT;
$p = getAuthLevel($cp->auth, 'tickets');
$admin = $p['group_level'] >= $level ? 1 : 0;
$user = $p['user_level'] >= $level ? 1 : 0;

// if($admin + $user < 1){
// 	print "You don't have permissions to do this. Sorry.";
// 	done();
// }

print $cp->selfURL('Make Family Tickets', 
				   array('action' => 'makefamilytickets'));
print $cp->selfURL('Make Paddles for all tickets', 
				   array('action' => 'makepaddles'));
print $cp->selfURL('Make Blank Paddles', 
				   array('action' => 'makeblanklines'));

// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){

	//// MAKE FAMILY TICKETS
 case 'makefamilytickets':
	 $fam =& new CoopObject(&$cp, 'families', &$none);
	 // note: account_number = 2 is quilt/food fee. you get a ticket if you drop.
	 $fam->obj->query("
 select families.* 
from families
    left join kids on families.family_id = kids.family_id 
    left join enrollment on kids.kid_id = enrollment.kid_id
    left join families_income_join 
        on families_income_join.family_id = families.family_id
    left join income on 
        families_income_join.income_id = income.income_id
where enrollment.school_year = '$sy'
    and ((enrollment.dropout_date < '2000-01-01'
            or enrollment.dropout_date is null)
        or (account_number = 2 and payment_amount > 0 
            and income.school_year = '$sy'))
group by families.family_id
order by families.name;
			");
	 //$fam->obj->debugLevel(2);

	 //look for tickets entry this year. none there? add 2 tickets.
	 while($fam->obj->fetch()){
		 $tic =& new CoopObject(&$cp, 'tickets', &$none);
		 $tic->obj->{$fam->pk} = $fam->obj->{$fam->pk};	// THIS fam
		 $sav = $tic->obj;
		 if($tic->obj->find() < 1){
			 // check for fee paid!!
			 $finj = new CoopObject(&$cp, 'families_income_join', &$top);
			 $finj->obj->{$fam->pk} = $fam->obj->{$fam->pk};	// THIS fam
			 $inc = new CoopObject(&$cp, 'income', &$finj);
			 $inc->obj->account_number = 2; // food-quilt fee
			 $inc->obj->school_year = $sy;
			 $inc->obj->joinAdd($finj->obj);
			 $found = $inc->obj->find();
			 if(!$found){
				 //  now check for indulgences... damn this is fucked up
				 $ind = new CoopObject(&$cp, 'nag_indulgences', &$top);
				 $ind->obj->{$fam->pk} = $fam->obj->{$fam->pk};	// THIS fam
				 $ind->obj->school_year = $sy;
				 $ind->obj->whereAdd('indulgence_type = "Everything"  or
						indulgence_type = "Quilt Fee"');
				 $found = $ind->obj->find();
			 }
			 $tq = $found > 0 ? 2 : 1;
			 $sav->ticket_quantity = $tq;
			 $sav->ticket_type_id = 3; // member ticket
			 $sav->school_year = $sy;
			 //XXX if i move this to the object, yank the printf!
			 printf("<br>Inserting %d tickets for %s family...", 
					$tq, $fam->obj->name);
			 $sav->insert();
			 $added++;
		 }
	 }
	 if(!$added){
		 print "<br>Done. No new families or new payments of fees. No 'free' tickets need to be added. If that seems wrong, make sure the new families have been added, or that their Quilt/Surfboard fees have been paid and entered.<br>";
	 }
	 
	 break;

//////MAKE PADDLES
 case  'makepaddles':
	 $atd->obj->school_year = $sy;
	 $atd->obj->find();
	 while($atd->obj->fetch()){
		 //confessObj($atd->obj, 'atd');
		 $atd->obj->updatePaddles(&$cp);
	 }
	 break;

 case 'makeblanklines':
	 $blanks = 50;
 	 $pad = new CoopObject(&$cp, 'springfest_attendees', &$nothing);
	 //$pad->obj->debugLevel(2);
	 $pad->obj->whereAdd('(ticket_id is null or ticket_id < 1)');
	 $pad->obj->school_year = $sy;
	 $found = $pad->obj->find();
	 print "<br>$found blank paddles found. ";
	 $tomake = $blanks - $found;
	 $tomake > 0 && print "<br>Adding $tomake blank lines... ";
	 while($tomake-- > 0){
		 $pad = new CoopObject(&$cp, 'springfest_attendees', &$nothing);
		 $pad->obj->insert();
	 }

	 print "Done.<br>";
	 break;

		
 default:
	 //print showList(&$atd);
	 break;
}




done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE TICKETS -->


