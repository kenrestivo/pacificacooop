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

	// soundex search, using mysql to help me
	$sub = new CoopObject(&$cp, 'companies', &$dop);
	$sub->obj->selectAdd(sprintf('soundex(company_name) = soundex("%s") as sdx',
								 $co->obj->escape($co->obj->company_name)));
	$sub->obj->whereAdd(sprintf(' %s != %d',
								$co->pk, $co->obj->{$co->pk}));
	$sub->obj->find();

	while($sub->obj->fetch()){
		if($sub->obj->sdx && !in_array($co->obj->{$co->pk}, $dupefound)){
			$dupefound[] = $sub->obj->{$sub->pk};
			printf("<br>hey %s dupes ", $co->obj->company_name);
			printf("are %s", $sub->obj->company_name);
		}		
	}
	

// 		$perc = 0;
// 		similar_text($co->obj->company_name, $sub->obj->company_name, &$perc);
// 		if($perc > 70){


// 	}
	
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END FIND DUPES  -->


