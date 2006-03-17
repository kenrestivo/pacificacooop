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
print $cp->pageTop();

$atd = new CoopView(&$cp, 'tickets', $none);
$sy=findSchoolYear();
 

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print $cp->stackPath();


print "<p>Springfest Family Tickets</p>";

if(!checkAuthLevel($cp->auth, 0, 'tickets', ACCESS_EDIT, 
				   $cp->userStruct)){
 	print "You don't have permissions to do this. Sorry.";
 	done();
}


print $cp->selfURL(array('value'=> 'Make Family Tickets', 
                         'inside' => array('action' => 'makefamilytickets')));
print $cp->selfURL(array('value' => 'Make Paddles for all tickets', 
                         'inside' => array('action' => 'makepaddles')));
print $cp->selfURL(array('value' => 'Make Blank Paddles', 
                         'inside' => array('action' => 'makeblanklines')));

// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){

	//// MAKE FAMILY TICKETS
 case 'makefamilytickets':
	 $fam =& new CoopObject(&$cp, 'families', &$none);
	 // note: account_number = 2 is quilt/food fee. you get a ticket if you drop.
	 $fam->obj->query(sprintf('
select distinct *
                    from families
                        left join kids on families.family_id = kids.family_id 
                        left join enrollment on kids.kid_id = enrollment.kid_id 
                    where enrollment.school_year = "%s"
                    and (enrollment.dropout_date < "1900-01-01"
                       or enrollment.dropout_date > now()
                        or enrollment.dropout_date is null)
                    group by families.family_id
                    order by families.name',
                              $sy));
	 //$fam->obj->debugLevel(2);

	 //look for tickets entry this year. none there? add 2 tickets.
	 while($fam->obj->fetch()){
 		 $tic =& new CoopObject(&$cp, 'tickets', &$none);
		 $tic->obj->whereAdd(sprintf('%s = %d and school_year = "%s"',
                                     $fam->pk,
                                     $fam->obj->{$fam->pk},
                                     $sy));	// THIS fam
         $tic->obj->find();
		 if($tic->obj->N < 1){
             $sav =& new CoopForm(&$cp, 'tickets', &$none);
             $sav->obj->{$fam->pk} = $fam->obj->{$fam->pk};	// THIS fam
			 $sav->obj->ticket_quantity = 2;
			 $sav->obj->ticket_type_id = 3; // member ticket
			 $sav->obj->school_year = $sy;

             // now link in the fee they paid
             $inc =& new CoopObject(&$cp, 'income', &$none);
             $inc->obj->query(
                 sprintf('select * from income left join families_income_join on income.income_id = families_income_join.income_id where family_id = %d and school_year = "%s" and account_number = %d',
                         $fam->obj->{$fam->pk},
                         $sy,
                     COOP_FOOD_FEE));
             if($inc->obj->N > 0){
                 $inc->obj->fetch();
                 $sav->obj->{$inc->pk} = $inc->obj->{$inc->pk};
             }

			 //XXX if i move this to the object, yank the printf!
			 printf("<br>Inserting %d tickets for %s family...", 
					$sav->obj->ticket_quantity, $fam->obj->name);
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
     /// XX broken. step thorugh the tickets first!
     $atd =& new CoopObject(&$cp, 'tickets', &$none);
	 $atd->obj->whereAdd(sprintf('school_year = "%s", $sy'));
	 $atd->obj->find();
	 while($atd->obj->fetch()){
		 //confessObj($atd->obj, 'atd');
         $atd->id = $atd->obj->{$atd->pk}; // XXX hack!
		 $atd->obj->updatePaddles(&$atd);
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


