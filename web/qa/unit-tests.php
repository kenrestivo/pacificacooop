<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('first.inc');
require_once('CoopPage.php');
require_once('CoopObject.php');
require_once 'PHPUnit.php';
require_once 'PHPUnit/GUI/HTML.php';
require_once('utilstest.php');

//TODO: write a function to include everything in this dir
require_once('Table_permissions_test.php');

// MAYBE automate this part too? login/uid?
$cp = new coopPage( $debug);
$cp->title = 'Unit Tests for Co-Op';
$cp->heading = 'Unit Tests';
print $cp->pageTop();


// include file with object under test,
// each including no-arg functions named testFOO
// docs can go inside of classes. kewl.
foreach(array('UtilsTest', 'Table_permissions_test') as $suitename){
    $suites[]  = new PHPUnit_TestSuite($suitename);
}
$g = new PHPUnit_GUI_HTML($suites);
print $g->show();

done ();


////KEEP EVERTHANG BELOW

?>


<!-- END UNITTTEST -->

