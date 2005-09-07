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


    function test_HUMANdateNOYTK() {
        $this->assertEquals('2005-01-01', 
                            human_to_sql_date('01/01/05'));
    }


    function test_HUMANdateSHORTNOYTK() {
        $this->assertEquals('2005-01-01',
                            human_to_sql_date('1/1/05'));
    }


    /// weird case of passing a time to sqldate
    function test_HUMANtimeALREADYPM() {
        $this->assertEquals('01/01/2005 12:00PM',
                            sql_to_human_date('01/01/2005 12:00PM'));
    }

    function test_HUMANtimeALREADY() {
        $this->assertEquals('01/01/2005 12:00',
                            sql_to_human_date('01/01/2005 12:00'));
    }

    function test_HUMANtimeALREADYPMSHORT() {
        $this->assertEquals('01/01/2005 1:00AM',
                            sql_to_human_date('01/01/2005 1:00AM'));
    }


    function test_HUMANtimeALREADYPMSHORTSPACE() {
        $this->assertEquals('01/01/2005 1:00 AM',
                            sql_to_human_date('01/01/2005 1:00 AM'));
    }

    //// THE DATETIME
    function test_HUMANdatetime() {
        $this->assertEquals('2005-01-01 12:00',
                            human_to_sql_timestamp('01/01/2005 12:00'));
    }

   function test_HUMANdatetimedeconds() {
        $this->assertEquals('2005-01-01 12:00',
                            human_to_sql_timestamp('01/01/2005 12:00:00'));
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


    function test_HUMANdatetimePM() {
        $this->assertEquals('2005-01-01 23:00', 
                            human_to_sql_timestamp("01/01/2005 11:00PM"));
    }

    function test_HUMANdatetimeSHORTPM() {
        $this->assertEquals('2005-01-01 13:00', 
                            human_to_sql_timestamp("1/1/2005 1:00PM"));
    }

    function test_HUMANdatetimeSPACEPM() {
        $this->assertEquals('2005-01-01 23:00',
                            human_to_sql_timestamp('01/01/2005 11:00 PM'));
    }

    function test_HUMANdatetimeSHORTspacePM() {
        $this->assertEquals('2005-01-01 13:00',
                            human_to_sql_timestamp('01/01/2005 1:00 PM'));
    }

    function test_HUMANdatetimelcSPACEPM() {
        $this->assertEquals('2005-01-01 23:00', 
                            human_to_sql_timestamp('01/01/2005 11:00 pm'));
    }

    function test_HUMANdatetimelcSHORTspacePM() {
        $this->assertEquals('2005-01-01 13:00', 
                            human_to_sql_timestamp('01/01/2005 1:00 pm'));
    }

    function test_HUMANdatetimelcPM() {
        $this->assertEquals('2005-01-01 23:00', 
                            human_to_sql_timestamp("01/01/2005 11:00pm"));
    }

    function test_HUMANdatetimelcSHORTPM() {
        $this->assertEquals('2005-01-01 13:00', 
                            human_to_sql_timestamp('1/1/2005 1:00pm'));
    }

    /// ALSO ADD VARIATIONS THAT TEST FOR ALREADY OK!
    // i.e. passing an sqldate to humantosqldate, and vice versa

}


?>


<!-- END MIGRATEPERMS -->


