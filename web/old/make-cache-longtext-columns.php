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
                $fieldname = $lucy->obj->Field;
                printf('alter table %s add column %s_cache varchar(255);<br />',
                       $table, $fieldname);
                // OK populate it now
                if($_REQUEST['populate']){
                    $targ =& new CoopObject(&$cp, $table, &$nothing);
                    $targ->obj->find();
                    while($targ->obj->fetch()){
                        $targ->obj->{$fieldname. '_cache'} = 
                            sprintf('%.200s', 
                                    unHTML(strip_tags($targ->obj->$fieldname)));
                    }
                }
            }
        }
    }
}

////KEEP EVERTHANG BELOW
$cp =& new CoopPage($debug);

print $cp->selfURL(array('value' => 'Refresh'));


makeColumns($cp);

print $cp->selfURL(array('value' => 'Click here to populate data',
                         'inside' => array('populate' => 'yes')));

?>


