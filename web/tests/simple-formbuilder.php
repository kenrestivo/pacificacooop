<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');




$debug = 0;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();



print $cp->selfURL('refresh me goddammit');

// cheap dispatcher
confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 case 'edit':
 case 'process':

	 $obj =& DB_DataObject::factory('ads');
	 $fb = DB_DataObject_FormBuilder::create(&$obj);
	 $form = $fb->getForm();
	 if($form->process(array(&$fb, 'processForm'), false)){
		 $form->freeze();
	 }
	 echo $form->toHTML();
	 break;
 default:
	 $atd = new CoopView(&$cp, 'springfest_attendees', $none);
	 print $atd->simpleTable();
	break;
}



done ();

////KEEP EVERTHANG BELOW

?>


<!-- END TEST -->


