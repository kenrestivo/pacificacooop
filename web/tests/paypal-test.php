<?php
chdir("../");

require_once('CoopPage.php');
require_once('PostPaypal.php');

// turns an "entirepost" from the db, into a valid test post



function splitUp($entirepost)
{
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

	return $posties;
}




function testButtons($posts)
{
	foreach($posts as $entirepost)
	{
		foreach(array( "http://www.pacificacoop.org/sf-dev/ipn.php",
					   "http://www/coop-dev/thankyou.php") 
				as $formurl)
		{
			print "<p>THIS WILL SEND $entirepost <br> TO <b>$formurl</b> . are you SURE?";
			
			printf('<form method="post" action="%s">', $formurl);
			print splitUp($entirepost);
			print '<input type="submit" value="Test IPN">';
			print "</form></p><hr>";
		}
		
	} 

}// end testbuttons





//////////////////// MAIN
testButtons(array(
			"[mc_gross]='45.00',[address_status]='confirmed',[payer_id]='E87MA7BES46JG',[tax]='0.00',[address_street]='923 Crespi Drive',[payment_date]='11:06:27 Nov 10, 2004 PST',[payment_status]='Completed',[address_zip]='94044',[first_name]='Lynn',[mc_fee]='1.61',[address_name]='Lynn Schuette',[notify_version]='1.6',[custom]='fid90:coa2',[payer_status]='verified',[business]='beecooke@yahoo.com',[address_country]='United States',[address_city]='Pacifica',[quantity]='1',[payer_email]='lizacolby1@yahoo.com',[verify_sign]='A-.rN679Fa58w112CZLxIzmi6U7qA0FpRaLXR.fCsaIGGX8josktSV1N',[txn_id]='7D282392D90946420',[payment_type]='instant',[last_name]='Schuette',[address_state]='CA',[receiver_email]='beecooke@yahoo.com',[payment_fee]='1.61',[receiver_id]='99EH7EZNMZF9Y',[txn_type]='web_accept',[item_name]='Springfest Food/Raffle Fee',[mc_currency]='USD',[item_number]='',[payment_gross]='45.00'",
			
			"[txn_type]='web_accept',[payment_date]='17:36:11 Jan 24, 2005 PST',[last_name]='blowzinski',[pending_reason]='unilateral',[item_name]='Springfest Donation',[payment_gross]='399.00',[mc_currency]='USD',[business]='pacificanurseryschool@yahoo.com',[payment_type]='instant',[payer_status]='verified',[verify_sign]='AGVkn-PR0iI-HRZsgj-C82.m9uEZAJsa-rQBV0lz9EAYGDaiPC9E76zV',[txn_id]='0',[test_ipn]='1',[payer_email]='krestivo@restivo.org',[tax]='0.00',[receiver_email]='pacificanurseryschool@yahoo.com',[quantity]='1',[first_name]='joe',[payer_id]='YZPYHQP4CP9Z2',[item_number]='invitations',[payment_status]='Pending',[mc_gross]='399.00',[custom]='lid232:coa7',[notify_version]='1.6'",
			
			"[txn_type]='web_accept',[payment_date]='17:24:55 Jan 24, 2005 PST',[last_name]='blowzinski',[pending_reason]='unilateral',[item_name]='Springfest Tickets (2)',[payment_gross]='50.00',[mc_currency]='USD',[business]='pacificanurseryschool@yahoo.com',[payment_type]='instant',[payer_status]='unverified',[verify_sign]='AomqsKH31qYQ8H7fnY.6pitYpbRlAHoDJE8kGTu86RhlTQbUuWpncjLI',[txn_id]='0',[test_ipn]='1',[payer_email]='krestivo@restivo.org',[tax]='0.00',[receiver_email]='pacificanurseryschool@yahoo.com',[quantity]='1',[first_name]='joe',[invoice]='2',[payer_id]='YZPYHQP4CP9Z2',[item_number]='invitations',[payment_status]='Pending',[mc_gross]='50.00',[custom]='lid88:coa6',[notify_version]='1.6'"
				));


$cp = new CoopPage();
// list of transactions. manually frob posttransaction on it
switch($_REQUEST['action'])
{
 case 'test':
	 $pp = new PostPaypal();
	 print $pp->postTransaction($_REQUEST['uid']);
 case 'list':
 default:
	 print $cp->selfURL('test 16', 'action=test&uid=16');
	 print $cp->selfURL('test 17', 'action=test&uid=17');
	 break;
}

print "DONE";

?>

<!-- END TEST -->


