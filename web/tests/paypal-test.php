<?php

// turns an "entirepost" from the db, into a valid test post

//$formurl = "http://www.pacificacoop.org/sf-dev/ipn.php";
$formurl = "http://www/coop-dev/thankyou.php";

$entirepost = "[mc_gross]='45.00',[address_status]='confirmed',[payer_id]='E87MA7BES46JG',[tax]='0.00',[address_street]='923 Crespi Drive',[payment_date]='11:06:27 Nov 10, 2004 PST',[payment_status]='Completed',[address_zip]='94044',[first_name]='Lynn',[mc_fee]='1.61',[address_name]='Lynn Schuette',[notify_version]='1.6',[custom]='fid90:coa2',[payer_status]='verified',[business]='beecooke@yahoo.com',[address_country]='United States',[address_city]='Pacifica',[quantity]='1',[payer_email]='lizacolby1@yahoo.com',[verify_sign]='A-.rN679Fa58w112CZLxIzmi6U7qA0FpRaLXR.fCsaIGGX8josktSV1N',[txn_id]='7D282392D90946420',[payment_type]='instant',[last_name]='Schuette',[address_state]='CA',[receiver_email]='beecooke@yahoo.com',[payment_fee]='1.61',[receiver_id]='99EH7EZNMZF9Y',[txn_type]='web_accept',[item_name]='Springfest Food/Raffle Fee',[mc_currency]='USD',[item_number]='',[payment_gross]='45.00'";

preg_match_all("/\[.+?\]='.*?'/", $entirepost, $entiresplit);
//print_r($entiresplit);
reset($entiresplit);
foreach ($entiresplit[0] as $trash=>$pair){
    preg_match("/^\[(.+?)\]='(.*?)'/", $pair, $matches);
    $all[$matches[1]] = $matches[2];
}
//print_r($all);
//reset($all);
foreach($all as $key=>$val){
    $posties .= sprintf('<input type="hidden" name="%s" value="%s">', 
                        $key, $val);
}

print "THIS WILL SEND PAYPAL DATA TO $formurl . are you SURE?";

printf('<form method="post" action="%s">', $formurl);
print $posties;
print '<input type="submit" value="Test IPN">';
print "</form>";





?>

<!-- END TEST -->


