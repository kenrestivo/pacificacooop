<?php 

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once("utils.inc");
require_once('CoopPage.php');
require_once('CoopView.php');

$debug = 1;

//MAIN
//$_SESSION['toptable'] 



$cp = new coopPage( $debug);
$cp->pageTop();


foreach(array("families_income_join", "leads") as $table){
	$view = new CoopView(&$cp, $table);
		// XXX hack! use the get() form instead if you know index
		//$pk = $view->getPK(); 
	$u = getUser($cp->auth['uid']);
	$view->obj->family_id = $u['family_id'];
	print $view->simpleTable();
}


done ();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->


