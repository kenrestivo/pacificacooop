<?php 

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once("utils.inc");
require_once('CoopPage.php');
require_once('CoopView.php');

$debug = 0;

//MAIN
//$_SESSION['toptable'] 



$cp = new coopPage( $debug);
$cp->pageTop();


if(0){
 foreach(array("families", "leads") as $table){
 	$view = new CoopView(&$cp, $table);
 		// XXX hack! use the get() form instead if you know index
 		//$pk = $view->getPK(); 
 	$u = getUser($cp->auth['uid']);
 	$view->obj->family_id = $u['family_id'];
 	print $view->simpleTable();
 }

} else {
	$cid = 114;
	foreach(array("companies", 'flyer_deliveries') as $table){
		$view = new CoopView(&$cp, $table);
		$view->obj->company_id = $cid;
		print $view->simpleTable();
	}
		
	
//'companies_income_join', 'companies_auction_join', 
}

done ();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->





