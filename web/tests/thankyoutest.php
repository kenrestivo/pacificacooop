<?php

//$Id$


function thankyoutest($url, $name, $all)
{
	foreach($all as $key=>$val){
		$posties .= sprintf('<input type="hidden" name="%s" value="%s">', 
							$key, $val);
	}
	$res .= sprintf('<form method="post" action="%s">', $url);
	$res .= $posties;
	$res .= sprintf('<input type="submit" value="%s">', $name);
	$res .= "</form>";
	
	return $res;
}



////////////MAIN

$formurl = "http://www/coop-dev/thankyou.php";
//$formurl = "http://www.pacificacoop.org/sf/thankyou.php";

print "<html><head><title>thank you note test</title></head>";
print "<body>";

print "<p>click below to test various thankyou notes:</p>";
print thankyoutest($formurl, "Test PayPal Thank You",
				   array('payment_gross' => "30",
						 'address_street' => '112 Any Street',
						 'address_city' => 'Pacifica',
						 'address_state' => 'CA',
						 'address_zip' => "94044",
						 'first_name' => 'Test',
						 'last_name' => 'Donor',
						 'address_country' => 'USA',
						 'confirm_date' => '20041110220022'
					   ));

print "</body></html>";


////KEEP EVERTHANG BELOW

?>


<!-- END THANKYOU TEST -->


