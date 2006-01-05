<?php

//$Id$
// to move a few auction items over to in-kind items.

chdir("../");                   // XXX only for "test" dir hack!

require_once("CoopObject.php");


function makeColumns(&$cp)
{
 
// get tables
    $tab =& new CoopObject(&$cp, 'table_permissions', &$nothing);
    $tab->obj->query("show tables");
    while($tab->obj->fetch()){
        $table  = $tab->obj->Tables_in_coop;
        $lucy =& new CoopObject(&$cp, 'table_permissions', &$nothing);
        $lucy->obj->query("explain $table");
        while($lucy->obj->fetch()){
            //confessArray($row, "row $table");
            if($lucy->obj->Type == 'longtext'){
                printf('alter table %s add column %s_cache varchar(255);<br />',
                       $table, $lucy->obj->Field);
                // OK populate it now
            }
        }
    }
}

////KEEP EVERTHANG BELOW
$cp =& new CoopPage($debug);
makeColumns($cp);



?>


