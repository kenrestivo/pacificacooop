<?php

//$Id$
// to find last years' alumni and add them to the leads db.
// I HATE THIS!!! people should be people, dammit.

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');


$debug = 0;

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();

	
$doit = $_REQUEST['do_it'];

printf("<p>This script %s the following updates:</p>",
	   $doit? "has made" : "will make");


$top = new CoopObject(&$cp, 'enrollment', $none);
// i hope i fix the db schema so i'll never have do to this again
// but if i did, the right thing would be to search for "not this year"
$top->obj->school_year = '2003-2004';
$top->obj->find();
//print $top->simpleTable();

while($top->obj->fetch()){
	$top->obj->getLinks();

	$kids =& $top->obj->_kid_id;

 	$newenrol = new CoopObject(&$cp, 'enrollment', $none);
 	$newenrol->obj->school_year = '2004-2005';

 	$newkid = new CoopObject(&$cp, 'kids', $none);
 	$newkid->obj->family_id = $kids->family_id;
	
 	$newenrol->obj->joinAdd($newkid->obj);

	
	$found = $newenrol->obj->find(true);
	

	if(!$found){
		// families that weren't here last year
		//now add their addresses to leads!
		
		$top->obj->_kid_id->getlinks();
		$family =& $top->obj->_kid_id->_family_id;
		//confessObj($family, "alumni-family");

		//XXX! i'm assuming they're not already in leads db. fuck.
		// this could create HUGE duplication problems dude
		
		$lead = new CoopObject(&$cp, 'leads', $none);


		// got one! now... let's dump all the info in
		if($family->address1){
			$lead->obj->source_id = 7; // ken's  temporary alumni hack
			$lead->obj->first_name = sprintf("%s Family", $family->name);
			$lead->obj->address1 = $family->address1;
			$lead->obj->email_address = $family->email;
			$lead->obj->phone = $family->phone;

			$lead->obj->state = "CA";

			$lead->obj->city = "Pacifica";
			$lead->obj->zip = "94044";
			// check for zip code, and either parse out or flag
			if(preg_match('/^(.+?)(\d{5})/', $lead->obj->address1, $matches)){
				$lead->obj->zip = $matches[2];
				$lead->obj->address1 = $matches[1];
				$cityar = explode(" ", $matches[1]);
				$cityar = array_reverse($cityar);

				//TODO yank the duplicated city stuff? or do it manually?
				if($cityar[1] == 'City'){
					$lead->obj->city = $cityar[2] . " " . $cityar[1];
				} else {
					$lead->obj->city = $cityar[1];
				}
			}
			
			
	
			printf("Insert%s %s with %s %s %s %s<br>",
				   $doit ? 'ed' : "",
				   $lead->obj->first_name,
				   $lead->obj->address1,
				   $lead->obj->city,
				   $lead->obj->zip,
				   $doit ? '' : "?");
			$total++;
			if($doit){
				$lead->obj->insert();
			}

		}
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

