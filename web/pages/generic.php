<?php

//$Id$


require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');



PEAR::setErrorHandling(PEAR_ERROR_PRINT);

//$debug = 4;


$cp = new coopPage( $debug);
print $cp->pageTop();


$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

$atd = new CoopView(&$cp, $_REQUEST['table'], $none);

printf("<p>%s</p>",$atd->obj->fb_formHeaderText);


print "\n<hr></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div id="centerCol">';


function viewHack(&$cp, &$atd)
{

	 return $atd->simpleTable();
			
}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
//// EDIT AND NEW //////
 case 'new':
 case 'add':					//  for OLD menu system
 case 'edit':
	 // NOT the coopView above!
	 $atdf = new CoopForm(&$cp, $_REQUEST['table'], $none); 


	 $atdf->build($_REQUEST);


	 // ugly assthrus for my cheap dispatcher
	 $atdf->form->addElement('hidden', 'action', 'edit'); 
	 $atdf->form->addElement('hidden', 'table', $_REQUEST['table']); 

	 $atdf->legacyPassThru();

	 $atdf->addRequiredFields();
	 
	 // make-um bigger

	 
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

//// DETAILS //////
 case 'details':

	$top = new CoopView(&$cp, $_REQUEST['table'], &$nothing);
	 $id = $_REQUEST[$top->prependTable($top->pk)];
	$top->obj->{$top->pk} = $id;
	$top->obj->find(true);		//  XXX aack! need this for summary
	print $top->horizTable();

	// standard audit trail, for all details
	$aud =& new CoopView(&$cp, 'audit_trail', &$top);
	$aud->obj->table_name = $top->table;
	$aud->obj->index_id = $id;
	$aud->obj->orderBy('updated desc');
	print $aud->simpleTable();

	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp, &$atd);
	 break;
}



done ();

////KEEP EVERTHANG BELOW

?>


<!-- END GENERIC -->


