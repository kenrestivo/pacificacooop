<?php 

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once("utils.inc");
require_once('CoopPage.php');
require_once('CoopView.php');

$debug = 0;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();

$sy = findSchoolYear();

$top = new CoopView(&$cp, 'companies');


// $top->obj->query("select * from companies         
//         left join companies_income_join 
//                 on companies.company_id = 
//                     companies_income_join.company_id
// 		left join income
//                on income.income_id = companies_income_join.income_id
// 		where income.income_id is not null and income.school_year = '$sy'
// ");

//print "FOUND " . $top->obj->find();

$cij = new CoopObject(&$cp, 'companies_income_join');

//$inc = new CoopObject(&$cp, 'income');
//$cij->obj->joinAdd($inc->obj);

$top->obj->joinAdd($cij->obj);

//$top->obj->whereAdd('school_year = "2004-2005"');

$acj = new CoopObject(&$cp, 'companies_auction_join');
//$acj->obj->whereAdd('school_year = "2004-2005"');
$top->obj->joinAdd($acj->obj);

print $top->simpleTable($summary);
	


done ();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->





