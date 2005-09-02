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


    /// TO HUMAN, FROM MACHINE
    function test_SQLdate() {
        $this->assertEquals('01/01/2005', 
                            sql_to_human_date('2005-01-01'));
    }


    function test_SQLdatetime() {
        $this->assertEquals('01/01/2005 12:00PM',
                            timestamp_db_php('2005-01-01 12:00:00'));
                            
    }

    function test_HUMANdateALREADY() {
        $this->assertEquals('01/01/2005',
                            sql_to_human_date('01/01/2005'));
    }



    /// FROM HUMAN, TO MACHINE
    function test_HUMANdate() {
        $this->assertEquals('2005-01-01', 
                            human_to_sql_date('01/01/2005'));
    }


    function test_HUMANdateSHORT() {
        $this->assertEquals('2005-01-01',
                            human_to_sql_date('1/1/2005'));
    }

    function test_HUMANdatetime() {
        $this->assertEquals('2005-01-01 12:00',
                            human_to_sql_timestamp('01/01/2005 12:00'));
    }


    function test_HUMANdatetimeNOTIME() {
        $this->assertEquals('2005-01-01',
                            human_to_sql_timestamp('01/01/2005'));
    }

    function test_HUMANdatetimeNOTIMESHORT() {
        $this->assertEquals('2005-01-01',
                            human_to_sql_timestamp('1/1/2005'));
    }

    function test_HUMANdatetimeSHORT() {
        $this->assertEquals("2005-01-01 12:00", 
                            human_to_sql_timestamp('1/1/2005 12:00'));
    }


}


?>


<!-- END MIGRATEPERMS -->


