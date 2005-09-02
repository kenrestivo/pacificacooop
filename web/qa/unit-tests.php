<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopObject.php');
require_once 'PHPUnit.php';
require_once 'PHPUnit/GUI/HTML.php';
require_once('utils.inc');

// MAYBE automate this part too? login/uid?
$cp = new coopPage( $debug);
print $cp->pageTop();


// include file with object under test,
// each including no-arg functions named testFOO
// docs can go inside of classes. kewl.
$suite  = new PHPUnit_TestSuite('MathTest');
$result = PHPUnit::run($suite);

$g = new PHPUnit_GUI_HTML(array($suite));
print $g->show();

done ();


////KEEP EVERTHANG BELOW

?>


<!-- END UNITTTEST -->

