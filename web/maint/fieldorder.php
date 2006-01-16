<?php

//$Id$
// show the field order

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopObject.php');
//require_once('HTML/Table.php');


$cp = new coopPage( $debug);
//print $cp->pageTop();

print date('m/d/Y');


$page = new CoopPage(4);
$co =& new CoopObject(&$page, 'table_permissions', &$nothing);


$co->obj->query(
    'select distinct table_name from table_permissions order by table_name');

while($co->obj->fetch()){
    $tables[] = $co->obj->table_name;
}


print '<p>A nifty utility to show you all the tables for which anyone has permissions, and the ORDER of the fields as they will be displayed, which is of course in the order determined by CoopObject::reorder()</p>';

            
foreach($tables as $table){
    $err = ''; 
    $status = ''; 
    $targ =& new CoopObject(&$page, $table, &$co);

    $labels = array();
    printf('<h3>%s (%s)</h3>', $targ->obj->fb_formHeaderText, $table);

    foreach($targ->reorder($targ->obj->fb_fieldLabels) as $key => $title){
        $labels[] = sprintf('%s (%s)', 
                            $title,
                            $key);
    }

    printf('<ul><li>%s</li></ul>', implode('</li><li>', $labels));
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SHOWSTRUCT -->
 

