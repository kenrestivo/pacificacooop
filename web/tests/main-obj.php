<?php 

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once("utils.inc");
require_once('CoopPage.php');
require_once('CoopView.php');

$debug = 0;

//MAIN
//$_SESSION['toptable'] 



$page = new coopPage( $debug);
$page->pageTop();


if(0){
 foreach(array("families", "leads") as $table){
 	$view = new CoopView(&$page, $table);
 		// XXX hack! use the get() form instead if you know index
 		//$pk = $view->getPK(); 
 	$u = getUser($page->auth['uid']);
 	$view->obj->family_id = $u['family_id'];
 	print $view->simpleTable();
 }

} else {
	$cid = 114;
	foreach(array("companies", 'flyer_deliveries') as $table){
		$view = new CoopView(&$page, $table);
		$view->obj->get($cid);
		print $view->simpleTable();
	}

	$co =& new CoopObject(&$page, 'companies_income_join');
	$co->obj->company_id = $cid;
	$real =& new CoopView(&$page, 'income');
	$real->obj->joinadd($co->obj);
	print $real->simpleTable();
	

	$co =& new CoopObject(&$page, 'companies_auction_join');
	$co->obj->company_id = $cid;
	$real =& new CoopView(&$page, 'auction_donation_items');
	$real->obj->joinadd($co->obj);
	print $real->simpleTable();
	
//'companies_income_join', 'companies_auction_join', 
}

done ();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->





