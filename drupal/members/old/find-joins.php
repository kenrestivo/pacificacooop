<?php

//$Id$
// to find last years' alumni and add them to the leads db.
// I HATE THIS!!! people should be people, dammit.


require_once('../includes/first.inc');
require_once('CoopPage.php');
require_once('CoopObject.php');


$cp = new coopPage( $debug);
//print $cp->pageTop();

print date('m/d/Y');


$page = new CoopPage(4);
$co =& new CoopObject(&$page, 'table_permissions', &$nothing);


$co->obj->query(
    'select distinct table_name from table_permissions');

while($co->obj->fetch()){
    $tables[] = $co->obj->table_name;
}


            
foreach($tables as $table){
    $err = ''; 
    $status = ''; 
    $targ =& new CoopObject(&$page, $table, &$co);
    foreach(array('family_id', 'school_year') as $linkcol){
        //print "HEY $table $linkcol [". $targ->obj->fb_joinPaths[$linkcol]. ']<br>';
        if($targ->obj->fb_joinPaths[$linkcol]){
            //print "foio";
            $status .= "$linkcol was in fb_joinPaths ";
        }
        $targ->joinTo($linkcol);
        if(count($targ->obj->fb_joinPaths[$linkcol]) != 1){
            ++$i;
            $err .= "NO $linkcol ";
        }

        if(count($targ->obj->fb_joinPaths[$linkcol]) > 1){
            ++$i;
            $err .= "TOO MANY $linkcol ";
        }

    }
    if($err){
        print "ERROR $table ======== $err, $status ===========<br>";
        confessArray($targ->obj->fb_joinPaths, $table);
    }
}
            
print "total errors found: $i<br>";

done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SHOWSTRUCT -->
 

