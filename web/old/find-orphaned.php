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
require_once('Sponsorship.php');




PEAR::setErrorHandling(PEAR_ERROR_PRINT);


//$debug = 2;

//MAIN


//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();
//$cp->createLegacy($cp->auth);

$targetTable = 'income';

$atd = new CoopView(&$cp, $targetTable, $none);
$atd->recordActions = array('details' => 'Details',
							'edit' => 'Edit');
$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Springfest Orphaned $targetTable</p>";

print $cp->selfURL('View');
print $cp->selfURL('Fix Zero Primary Keys', array('action' => 'unzero'));


function viewHack(&$cp, &$atd)
{
	 print "<p>The following entries are broken! They do not have anything associated with them</p>";
	 //confessArray($atd->backlinks, 'backlinks');
	 foreach($atd->backlinks as $table => $id){
		 $joins[] = sprintf("left join %s on %s.%s = %s.%s",
						   $table, 
						   $table, $id,
						   $atd->table, $id);
		 $sub = new CoopObject(&$cp, $table, &$atd);
		 $whereadd[] = sprintf("%s.%s is null", 
							  $table,
							  $sub->pk);
	 }
	 $query = sprintf("select %s.* from %s %s where %s",
					  $atd->table,
					  $atd->table,
					  implode(' ', $joins),
					  implode(' and ', $whereadd));
	 //print $query;
	 $atd->obj->query($query);
//  	 while($atd->obj->fetch()){
//  		 confessObj($atd->obj, 'atdobj');
//  	 }
	 return $atd->simpleTable(false);

}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){


//////// UNZERO //////////
 case 'unzero':
	 $top = new CoopObject(&$cp, $targetTable, &$nothing);
	 $top->obj->debugLevel(2);
	 $top->obj->whereAdd(sprintf("%s is null or %s < 1", 
								 $top->pk, $top->pk));
	 while($top->obj->fetch()){
		 $sub = new CoopObject(&$cp, $targetTable, &$nothing);
		 $sub->query(sprintf("select max %s from %s as maxid"),
					 $top->pk, $top->table);
		 $sub->obj->fetch(); // only one
		 $max = $sub->obj->maxid;
		 $old = $top->obj;
		 $new = $top->obj;
		 $new->obj->{$top->pk} = $max + 1;
		 $new->obj->update($old);
		 printf("<p>Updated %s...</p>",
				$top->concatLinkFields(&$new));
	 }

	 break;

//////// EDIT //////////
 case 'edit':
	 $atdf = new CoopForm(&$cp, $targetTable, $none); // NOT the coopView above!

	 
	 $atdf->build($_REQUEST);


	 // ugly assthrus for my cheap dispatcher
	 $atdf->form->addElement('hidden', 'action', 'edit'); 

	 $atdf->legacyPassThru();

	 $atdf->addRequiredFields();
	 

	 if ($atdf->form->validate()) {
		 print "saving...";
		 print $atdf->form->process(array(&$atdf, 'process'));
		 // gah, now display it again. they may want to make other changes!
		 print viewHack(&$cp, &$atd);
	 } else {
		 print $atdf->form->toHTML();
	 }
	 break;


////// DETAILS ///// 
 case 'details':
	$top = new CoopView(&$cp, $targetTable, &$nothing);
	//print "CHECKING $table<br>";
	$top->obj->{$top->pk} = $_REQUEST[$top->pk];
	$top->obj->find(true);		//  XXX aack! need this for summary
	print $top->horizTable();

	// standard audit trail, for all details
	$aud =& new CoopView(&$cp, 'audit_trail', &$top);
	$aud->obj->table_name = $callbacks['maintable'];
	$aud->obj->index_id = $cid;
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


<!-- END FIND ORPHANED -->


