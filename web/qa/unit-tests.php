<?php

//$Id$
// to find last years' alumni and add them to the leads db.
// I HATE THIS!!! people should be people, dammit.

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopObject.php');
require_once 'PHPUnit.php';
require_once 'PHPUnit/GUI/HTML.php';


$cp = new coopPage( $debug);
print $cp->pageTop();



class MathTest extends PHPUnit_TestCase {
    var $fValue1;
    var $fValue2;

    function MathTest($name) {
        $this->PHPUnit_TestCase($name);
    }

    function setUp() {
        $this->fValue1 = 2;
        $this->fValue2 = 3;
    }

    function testAdd() {
        $this->assertTrue($this->fValue1 + $this->fValue2 == 5);
    }
}

// $suite = new PHPUnit_TestSuite();
// $suite->addTest(new MathTest('testAdd'));
// $result = PHPUnit::run($suite);
// print $result->toHTML();


// include file with object under test,
// each including no-arg functions named testFOO
$suite  = new PHPUnit_TestSuite('MathTest');
$result = PHPUnit::run($suite);
//print $result->toHTML();

$g = new PHPUnit_GUI_HTML(array($suite));
print $g->show();

done ();


////KEEP EVERTHANG BELOW

?>


<!-- END UNITTTEST -->

