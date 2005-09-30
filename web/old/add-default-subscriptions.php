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
    $uid =& new CoopView(&$cp, 'users', $none);
    $uid->obj->family_id = $top->obj->family_id;
    $uid->obj->find(true);
    $subs =& new CoopView(&$cp, 'subscriptions', $none);
    $subs->obj->realm_id = 21; //XXX constant: blogs
    $subs->obj->user_id = $uid->obj->user_id;
    $found = $subs->obj->find();
    if(!$found){
        $total++;
        print "<br />{$top->obj->name}";
        if($doit){
            $subs =& new CoopView(&$cp, 'subscriptions', $none);
            $subs->obj->realm_id = 21; //XXX constant: blogs
            $subs->obj->user_id = $uid->obj->user_id;
            // The defaults
            $subs->obj->alerts =1;
            $subs->obj->new_entries =1;
            $subs->obj->changes =1;
            $subs->obj->insert();
            print " ...done";
        }
    }
}

print "<p></p>";

if(!$doit){
	print $cp->selfURL(array(
                           'value'=>
                           "YES Click here to approve and commit these $total changes",
                           'inside'=> 'do_it=yes'));
	print $cp->selfURL(array(
                           'value'=>"NO Click here to CANCEL"));
}
done ();

////KEEP EVERTHANG BELOW

?>


<!-- END IMPORTRASTA -->


