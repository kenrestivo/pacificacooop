<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopMenu.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');
require_once('DB/DataObject/FormBuilder.php');




$debug = 0;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();


$atd = new CoopView(&$cp, 'enrollment', $none);
$atd->recordActions = array('edit' => "Edit",
							'details' => "Details");

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Springfest Sponsorships</p>";

print $cp->selfURL('refresh me goddammit');


function viewHack(&$cp, &$atd)
{
	 $co =& new CoopObject(&$cp, 'enrollment', &$atd);
	 $atd->obj->school_year = findSchoolYear();
	 return $atd->simpleTable();
			
}

// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
	 
//// EDIT AND NEW //////
 case 'new':
 case 'edit':
	 $fb = DB_DataObject_FormBuilder::create(&$atd->obj);
	 $form = $fb->getForm();
	 if ($form->validate()) {
		 print "saving...";
		 print $form->process(array(&$fb, 'processForm'));
		 // gah, now display it again. they may want to make other changes!
		 print viewHack(&$cp, &$atd);
	 } else {
		 print $form->toHTML();
	 }
	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp, &$atd);

	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END FBTEST -->


