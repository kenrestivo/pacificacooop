<?php

//$Id$
// ugly hack to deal with default subscriptions

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');


$debug = 0;

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();

	
$doit = $_REQUEST['do_it'];

printf("<p>This script %s the following updates:</p>",
	   $doit? "has made" : "will make");


$top =& new CoopView(&$cp, 'families', $none);
$top->find(true); // my find, which includes constraints
while($top->obj->fetch()){
    print "{$top->obj->name}<br />";
}



if(!$doit){
	print $cp->selfURL(array('value'=>
                             "YES Click here to approve and commit these $total changes",
                             'inside'=> 'do_it=yes'));
	print $cp->selfURL(array('value'=>"NO Click here to CANCEL"));
}
done ();

////KEEP EVERTHANG BELOW

?>


<!-- END IMPORTRASTA -->


