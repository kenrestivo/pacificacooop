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

print $cp->selfURL('Make Family Tickets', array('action' => 'maketickets'));

// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 case 'maketickets':
	 $sy=findSchoolYear();
	 $fam =& new CoopObject(&$cp, 'families', &$none);
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
		 $tic->obj->{$fam->pk} = $fam->obj->{$fam->pk};
		 $sav = $tic->obj;
		 if($tic->obj->find() < 1){
			 $sav->ticket_quantity = 2;
			 $sav->ticket_type_id = 3; // member ticket
			 $sav->school_year = $sy;
			 //XXX if i move this to the object, yank the printf!
			 printf("Inserting 2 tickets for %s family...", 
					$fam->obj->name);
			 $sav->insert();
		 }
	 }


	 break;
		
 default:
	 //print showList(&$atd);
	 break;
}




done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE TICKETS -->


