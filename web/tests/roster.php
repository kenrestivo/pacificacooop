<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopMenu.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');



$debug = 2;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();


$atd = new CoopView(&$cp, 'enrollment', $none);
$atd->recordActions = array('edit' => "Edit",
							'details' => "Details");

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Roster Hack</p>";

print $cp->selfURL('refresh me goddammit');
print $cp->selfURL('Add New Student', array('action' => 'new'));


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
	 $atdf = new CoopForm(&$cp, 'enrollment', $none); // NOT the coopView above!

	 $atdf->overrides['families']['fb_linkConstraints'] = 0;

	 $atdf->build($_REQUEST);


	 // ugly assthrus for my cheap dispatcher
	 $atdf->form->addElement('hidden', 'action', 'edit'); 

	 $atdf->legacyPassThru();

	 $atdf->addRequiredFields();

	 
	 if ($atdf->validate()) {
		 print "saving...";
		 print $atdf->form->process(array(&$atdf, 'process'));
		 // gah, now display it again. they may want to make other changes!
		 print viewHack(&$cp, &$atd);
	 } else {
		 print $atdf->form->toHTML();
	 }

	 //confessArray($_DB_DATAOBJECT_FORMBUILDER, 'dbdofb');
	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp, &$atd);

	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END ROSTERTEST -->


