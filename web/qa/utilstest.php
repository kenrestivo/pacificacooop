<?php

//$Id$

//chdir("../");                   // XXX only for "test" dir hack!
require_once 'utils.inc';
require_once 'PHPUnit.php';

/// UTILS IS NOT OOP. if it was, i'd just add test_FOO into the class
class UtilsTest extends PHPUnit_TestCase {

    function UtilsTest($name) {
        $this->PHPUnit_TestCase($name);
    }

    function testFake() {
        $this->assertTrue(1);
    }

//     function testFailure() {
//         $this->assertTrue(0);
//     }

    function test_SQLdate() {
        $this->assertTrue(sql_to_human_date('2005-01-01') == '01/01/2005');
    }

    function test_SQLdateFLAG() {
        $this->assertTrue(sql_to_human_date('2005-01-01') == '01/01/2005');
    }

    function test_SQLdatetime() {
        $this->assertTrue(sql_to_human_date('2005-01-01 12:00:00', 1) == '01/01/2005 12:00:00');
    }

    function test_SQLdatetimeNOFLAG() {
        $this->assertTrue(sql_to_human_date('2005-01-01 12:00:00') == '01/01/2005 12:00:00');
    }

    function test_HUMANdate() {
        $this->assertTrue(sql_to_human_date('01/01/2005') == '01/01/2005');
    }

    function test_HUMANdateFLAG() {
        $this->assertTrue(sql_to_human_date('01/01/2005') == '01/01/2005');
    }


    function test_HUMANdatetime() {
        $this->assertTrue(sql_to_human_date('01/01/2005 12:00:00') == '01/01/2005 12:00:00');
    }


    function test_HUMANdatetimeFLAG() {
        $this->assertTrue(sql_to_human_date('01/01/2005 12:00:00',1) == '01/01/2005 12:00:00');
    }

    function test_HUMANdatetimeShort() {
        $this->assertTrue(sql_to_human_date('01/01/2005 12:00') == '01/01/2005 12:00');
    }

    function test_HUMANdatetimeShortFLAG() {
        $this->assertTrue(sql_to_human_date('01/01/2005 12:00') == '01/01/2005 12:00');
    }

}


?>


<!-- END MIGRATEPERMS -->


