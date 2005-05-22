<?php

//$Id$
// to move a few auction items over to in-kind items.

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');


$debug = 0;

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();

	
$doit = $_REQUEST['do_it'];

printf("<p>This script %s will move the following auction items into in-kind items:</p>",
	   $doit? "has made" : "will make");


//TODO: make this a chooser form list with checkboxes

$top = new CoopObject(&$cp, 'auction_donation_items', $none);
$top->obj->auction_donation_item_id = 187;
$top->obj->find();
//print $top->simpleTable();
while($top->obj->fetch()){
	$co = new CoopObject(&$cp, 'companies_auction_join', &$top);
	$co->obj->joinAdd($top->obj);
	$co->obj->find(true);
	$co->obj->getLinks();
	//confessObj($co->obj, 'co');

	printf('Ma%s "%s" for $%0.02f (%s) by %s into an in-kind donation%s<br>',
		   $doit ? 'de' : "ke",
		   $top->obj->item_description,
		   $top->obj->item_value,
		   $top->obj->school_year,
		   $co->obj->_company_id->company_name,
		   $doit ? '' : "?");
	$total++;
	
	if($doit){
		// create a new in-kind object with the old stuff
		$inkind = new CoopObject(&$cp, 'in_kind_donations', &$top);
		foreach(array('item_description', 'quantity', 'item_value', 
					  'date_received', 'school_year', 'thank_you_id') as $col)
		{
			$inkind->obj->$col = $top->obj->$col;
		}
//		confessObj($inkind->obj, 'newinkind');
		// insert it and grab the lastinsertid for it
		$inkind->obj->insert();
		$id = $inkind->lastInsertID();
		
		if(!$id){
			user_error("id $id was null!", E_USER_ERROR);
		}
		// create a join for it
		$join = new CoopObject(&$cp, 'companies_in_kind_join', &$top);
		$join->obj->in_kind_donation_id = $id;
		$join->obj->company_id = $co->obj->company_id;

		if(!($id && $co->obj->company_id)){
			user_error("cowardly refusing to enter non-null companyid and/or inkind id", E_USER_ERROR);
		}
		// insert the join
		$join->obj->insert();
		
		// delete the old auction item
		$top->obj->limit(1);
		$top->obj->delete();

		//delete the old join
		$top->obj->limit(1);
		$co->obj->delete();
	}

	
}


if(!$doit){
	print $cp->selfURL("YES Click here to approve and commit these $total changes",
				   "do_it=yes");
	print $cp->selfURL("NO Click here to CANCEL",
					   false, "index.php");
}
done ();

////KEEP EVERTHANG BELOW

?>


<!-- END IMPORTRASTA -->


