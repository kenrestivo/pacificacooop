<?php

//$Id$

require_once 'PHPUnit.php';

/// UTILS IS NOT OOP. if it was, i'd just add test_FOO into the class
class Table_permissions_test extends PHPUnit_TestCase {
    var $page;
    var $co ;
    var $tables  = array(); // cache of table names from query

    function Table_permissions_test($name) {
        $this->PHPUnit_TestCase($name);
    }

    function setUp()
        {
            $this->page = new CoopPage();
            $this->co =& new CoopObject(&$this->page, 
                                         'table_permissions', &$nothing);
        }

    function test_instantiations()
        {
            $this->assertTrue(is_a($this->page, 'CoopPage'));
            $this->assertTrue(is_a($this->co, 'CoopObject'));
        }

    function testFake() {
        $this->assertTrue(1);
    }

//     function testFailure() {
//         $this->assertTrue(0);
//     }


    function test_getTableList()
        {
            $this->co->obj->query(
                'select distinct table_name from table_permissions');
            while($this->co->obj->fetch()){
                $this->tables[] = $this->co->table_name;
            }
            $this->assertTrue(count($this->tables) > 0);
        }

    function test_familyJoins()
        {
            
            foreach(array('family_id', 'school_year') as $linkcol){
                foreach($this->tables as $table){
                    $targ =& new CoopObject(&$this->page, $table, &$this->co);
                    $this->assertTrue(is_a($targ, 'CoopObject'));
                    $targ->joinTo($linkcol);
                    $this->assertEquals(1, 
                                        count($targ->obj->fb_joinPaths[$linkcol]));
                }
            }
            
        }

}


?>


<!-- END MIGRATEPERMS -->


