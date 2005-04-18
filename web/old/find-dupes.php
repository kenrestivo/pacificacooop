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
		// don't do expensive similar_text if it's already a dupe
		if(!is_array($dupefound) || 
		   !in_array($co->obj->{$co->pk}, $dupefound))
		{	
			$perc = 0;
			similar_text($co->obj->company_name, 
						 $sub->obj->company_name, &$perc);
			if($sub->obj->sdx || $perc > 70){
				$dupefound[] = $sub->obj->{$sub->pk};
				printf("<br>does %s (%s %s) dupe %s (%s %s)?", 
					   $co->obj->company_name,
					   $co->obj->first_name,
					   $co->obj->last_name,
					   $sub->obj->company_name,
					   $sub->obj->first_name,
					   $sub->obj->last_name);
			}		
		}
	}
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END FIND DUPES  -->


