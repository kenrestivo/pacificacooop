<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!


require_once("CoopObject.php");


//// ugly, one-off hack to initialise the _cache tables with plain-text summaries of all html-capable longtext fields

function makeColumns(&$cp)
{
 
    $ignore = array('audit_trail');

    // get tables
    $tab =& new CoopObject(&$cp, 'table_permissions', &$nothing);
    $tab->obj->query("show tables");
    while($tab->obj->fetch()){
        $table  = $tab->obj->Tables_in_coop;
        if(in_array($table, $ignore)){
            continue;
        }
        $lucy =& new CoopObject(&$cp, 'table_permissions', &$nothing);
        $lucy->obj->query("explain $table");
        while($lucy->obj->fetch()){
            //confessArray($row, "row $table");
            if($lucy->obj->Type == 'longtext'){
                $fieldname = $lucy->obj->Field;
                printf('alter table %s add column _cache_%s varchar(255);<br />',
                       $table, $fieldname);
                // OK populate it now
                if($_REQUEST['populate']){
                    print "updating $table ...<br />";
                    $targ =& new CoopObject(&$cp, $table, &$nothing);
                    $targ->obj->find();
                    while($targ->obj->fetch()){
                        $hack =& new CoopObject(&$cp, $table, &$nothing);
                        $hack->obj->get($targ->obj->{$targ->pk});
                        /// XXX this is a stupid way to do it
                        /// i'm bypassing DataOBject->update()  because
                        ///  i forgot that i need to update the schema
                        /// so it wasn't working unless i forced it this way
                        $hack->obj->query(
                            sprintf('update %s set _cache_%s = "%.200s" where %s = %d limit 1', 
                                    $table, $fieldname, 
                                    mysql_real_escape_string(
                                        unHTML(
                                            strip_tags(
                                                $hack->obj->$fieldname))),
                                    $targ->pk,
                                    $targ->obj->{$targ->pk}));
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


