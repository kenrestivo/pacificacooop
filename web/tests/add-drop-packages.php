<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');
require_once 'HTML/QuickForm.php';
require_once('lib/advmultselect.php');


//PEAR::setErrorHandling(PEAR_ERROR_PRINT);

function fakeProcess($vars)
{
	confessArray($vars, 'fakeprocess');
}






$debug = 2;


$cp = new coopPage( $debug);
print $cp->pageTop();
//$cp->createLegacy($cp->auth);

$atd = new CoopView(&$cp, 'packages', $none);
$atd->recordActions = array('edit' => "Edit");
 

$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Springfest Package Add/Remove Test</p>";

print $cp->selfURL('Refresh');

//confessObj($cp, 'cp');
$level = ACCESS_EDIT;
$p = getAuthLevel($cp->auth, 'packaging');
$admin = $p['group_level'] >= $level ? 1 : 0;
$user = $p['user_level'] >= $level ? 1 : 0;

if($admin + $user < 1){
	print "You don't have permissions to do this. Sorry.";
	done();
}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 case 'edit':
	 $atd = new CoopForm(&$cp, 'packages', $none); // NOT the coopView above!
	 $atd->build($_REQUEST);


	 // ugly assthrus
	 $atd->form->addElement('hidden', 'action', 'edit'); 

	 $atd->legacyPassThru();

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


<!-- END ADD DROP PACKAGE TEST -->


