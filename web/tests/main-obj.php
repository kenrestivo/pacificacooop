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

$aij = new CoopObject(&$cp, 'companies_income_join');
$top->obj->joinAdd($aij->obj);

print $top->simpleTable($summary);
	


done ();

////KEEP EVERTHANG BELOW

?>
<!-- END MAIN OBJECT -->





