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

$mi= 'thank_you_id';
$cid = 1;

$top = new CoopView(&$cp, 'thank_you');
//print "CHECKING $table<br>";
$top->obj->$mi = $cid;
$summary = $top->getSummary();
print $top->simpleTable($summary);

// XXX this just SXCREAMS for a refactoring. a repetitive function.
// linktable, destinationtable, mainindex, and id?
$co =& new CoopObject(&$cp, 'companies_income_join');
$co->obj->$mi = $cid;
$real =& new CoopView(&$cp, 'income');
$real->obj->joinadd($co->obj);
$real->parentSummary = $summary;
print $real->simpleTable($summary);
	


done ();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->





