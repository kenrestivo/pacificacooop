<?php

//$Id$

//chdir("../");                   // XXX only for "test" dir hack!
require_once 'objects/Table_permissions.php';
require_once 'PHPUnit.php';

/// UTILS IS NOT OOP. if it was, i'd just add test_FOO into the class
class Table_permissions_test extends PHPUnit_TestCase {

    function Table_permissions_test($name) {
        $this->PHPUnit_TestCase($name);
    }

    function testFake() {
        $this->assertTrue(1);
    }

//     function testFailure() {
//         $this->assertTrue(0);
//     }


}


?>


<!-- END MIGRATEPERMS -->


