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


//PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$debug = 2;


$cp = new coopPage( $debug);
$cp->pageTop();
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
	 $atd->obj->get($_REQUEST[$atd->pk]);
	 print $atd->horizTable();

	 $form =& new HTML_QuickForm('auctionchooser');
	 $obj = DB_DataObject::factory('auction_donation_items'); 
	 if (PEAR::isError($obj)){
		 user_error("coopObject::constructor: " . $obj->getMessage(),
					E_USER_ERROR);
	 }

	 $obj->whereAdd('package_id < 1');
	 $obj->find();
	 while($obj->fetch()){
		 //confessObj($obj, 'aucob');
		 if((int)$obj->{$atd->pk} > 0){
			 $options[$obj->{$atd->pk}] =  
				 $obj->item_description;
		 }
	 }
	 confessArray($options, 'opts');
	 $form->addElement('select', 'auction_items', 
					   'Orphaned Auctions', &$options);

	 print $form->toHTML();

	 break;
		
		
 default:
	 $atd->obj->school_year = findSchoolYear();
	 print $atd->simpleTable();
	 break;
}



done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE PACKAGES -->


