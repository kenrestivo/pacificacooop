<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('DB/DataObject/Cast.php');


$debug = 0;


$cp = new coopPage( $debug);
$cp->pageTop();


$doit = $_REQUEST['do_it'];

printf("<p>This script %s the following updates to families:</p>",
	   $doit? "has made" : "will make");


$ld =& new CoopView(&$cp, 'leads', &$nothing);
$ld->obj->whereAdd('first_name = last_name');

if($doit){
	$ld->obj->find();
	while($ld->obj->fetch()){
		printf("<p>%s</p>", $old->last_name);
		$old = $ld->obj;
		$new = $ld->obj;
		$new->first_name =  DB_DataObject_Cast::sql('NULL');
		$new->update($old);
	}
} else {
	print $ld->simpleTable();

}
if(!$doit){
	print $cp->selfURL("YES Click here to approve and commit these $total changes",
					   "do_it=yes");


	print $cp->selfURL("NO Click here to CANCEL");
}
done ();

////KEEP EVERTHANG BELOW

?>


<!-- END FIXFIRSTLAST -->


