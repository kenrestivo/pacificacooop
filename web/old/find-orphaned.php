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
$cp->pageTop();
//$cp->createLegacy($cp->auth);

$atd = new CoopView(&$cp, 'income', $none);
$atd->recordActions = array('details' => 'Details');
$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Springfest Orphaned Income</p>";

print $cp->selfURL('View');


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
 case 'details':
	$top = new CoopView(&$cp, 'income', &$nothing);
	//print "CHECKING $table<br>";
	$top->obj->$mi = $cid;
	$top->createLegacy($callbacks);
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
	 print "<p>The following income entries are broken! They do not have anything associated with them</p>";
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
	 $query = sprintf("select * from %s %s where %s",
					  $atd->table,
					  implode(' ', $joins),
					  implode(' and ', $whereadd));
	 //print $query;
	 $atd->obj->query($query);
	 print $atd->simpleTable(false);

	 break;
}




done ();

////KEEP EVERTHANG BELOW

?>


<!-- END MAKE SPONSORSHIPS -->


