<?php

//$Id$
// to find last years' alumni and add them to the leads db.
// I HATE THIS!!! people should be people, dammit.

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopObject.php');
require_once("members.inc");
require_once("everything.inc");


$cp = new coopPage( $debug);
//print $cp->pageTop();

print date('m/d/Y');


$page = new CoopPage(4);
$co =& new CoopObject(&$page, 
                            'table_permissions', &$nothing);


$co->obj->query(
    'select distinct table_name from table_permissions');
while($co->obj->fetch()){
    $tables[] = $co->obj->table_name;
}


            
foreach($tables as $table){
    $err = '';
    $targ =& new CoopObject(&$page, $table, &$co);
    foreach(array('family_id', 'school_year') as $linkcol){
        $targ->joinTo($linkcol);
        if(count($targ->obj->fb_joinPaths[$linkcol]) != 1){
            ++$i;
            $err .= "$linkcol ";
        }
    }
    if($err){
        confessArray($targ->obj->fb_joinPaths, "$table ======== $err");
    }
}
            
print "total errors found: $i<br>";

done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SHOWSTRUCT -->
 

