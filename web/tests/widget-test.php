<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');
require_once 'HTML/QuickForm.php';
require_once('lib/customdatebox.php');


PEAR::setErrorHandling(PEAR_ERROR_PRINT);


$debug = 2;


$cp = new coopPage( $debug);
$cp->pageTop();
//$cp->createLegacy($cp->auth);

$table = 'auction_donation_items';

$atd = new CoopView(&$cp, $table, $none);
$atd->recordActions = array('edit' => "Edit");
 

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Widget Test</p>";

print $cp->selfURL('Refresh');


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 case 'edit':
	 $atd = new CoopForm(&$cp, $table, $none); // NOT the coopView above!

	 array_push($atd->obj->fb_fieldsToRender, 
				'date_received');

	 $atd->build($_REQUEST);

	 // ugly assthrus
	 $atd->form->addElement('hidden', 'action', 'edit'); 
	 $atd->form->addElement('hidden', $atd->pk, $atd->obj->{$atd->pk}); 

	 if($sid = thruAuthCore($cp->auth)){
		 $atd->form->addElement('hidden', 'coop', $sid); 
	 }
	 

	 $atd->addRequiredFields();

	 if ($atd->form->validate()) {
		 print "saving...";
		 $atd->form->process(array(&$atd, 'process'));
		 // gah, now display it again. they may want to make other changes!
		 print $cp->selfURL('Look again', 
							array('action' => 'edit',
								$atd->pk => $_REQUEST[$atd->pk]));
		 $atd->form->freeze();
	 }
	 print $atd->form->toHTML();


	 break;
		
		
 default:
	 $atd->obj->school_year = findSchoolYear();
	 print $atd->simpleTable();
	 break;
}



done ();

////KEEP EVERTHANG BELOW

?>


<!-- END WIDGET TEST -->


