<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('first.inc');
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');




//$debug = 2;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();

print $cp->selfURL('refresh (for testing)');


$co = new CoopObject(&$cp, 'companies', &$dop);
$co->obj->find();
while($co->obj->fetch()){
	//$co->obj->debugLevel(2);
	$sub = new CoopObject(&$cp, 'companies', &$dop);
	$sub->obj->whereAdd(sprintf('soundex(company_name) = soundex("%s") and %s != %d',
								$co->obj->escape($co->obj->company_name), 
								$co->pk, $co->obj->{$co->pk}));
	if($sub->obj->find() && !in_array($co->obj->{$co->pk}, $dupefound)){
		printf("<br>hey %s dupes", $co->obj->company_name);
		while($sub->obj->fetch()){
			$dupefound[] = $sub->obj->{$sub->pk};
			printf("are %s", $sub->obj->company_name);

		}
	}
	
	
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END FIND DUPES  -->


