<?php

#  Copyright (C) 2004  ken restivo <ken@restivo.org>
# 
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
# 
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details. 
# 
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

// copied from ipn tutorial

require_once("first.inc");


////////////////
class IPN
{
	var $paypal_id;				// the uid of the accounting_paypal entry


	// returns 1 if verified, 0 if invalid
	function verify()
		{
			// read the post from PayPal system and add 'cmd'
			$req = 'cmd=_notify-validate';

			foreach ($_POST as $key => $value) {
				$value = urlencode(stripslashes($value));
				$req .= "&$key=$value";
			}

			// post back to PayPal system to validate
			$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
			$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

			if (!$fp) {
				// HTTP ERROR
			} else {
				fputs ($fp, $header . $req);
				while (!feof($fp)) {
					$res = fgets ($fp, 1024);
					if (strcmp ($res, "VERIFIED") == 0) {
						$retval = 1;
// check the payment_status is Completed
// check that txn_id has not been previously processed
// check that receiver_email is your Primary PayPal email
// check that payment_amount/payment_currency are correct
// process payment
					}
					else if (strcmp ($res, "INVALID") == 0) {
// log for manual investigation
						$retval = 0
							}
				}
				fclose ($fp);
			}
			return $retval;
		} // END VERIFYWITHPAYPAL




	// returns insertid
	function save()
		{

// below supported vals that paypal posts to us,
// this list is exhaustive.. but
// without notify_version and verify_sign
// NOTE: if in is not in this array, it
// is not going in the database.

// OK screw this. genrate this instead from an 'explain' query
			$paypal_vals = 
				array("item_name", "receiver_email", "item_number", 
					  "invoice", "quantity", "custom", "payment_status", 
					  "pending_reason", "payment_date", "payment_gross", 
					  "payment_fee", "txn_id", "txn_type", "ipn_test",
					  "payer_id", "payer_business_name", "first_name", 
					  "last_name", "address_street", "address_city", 
					  "address_state", "address_zip", "address_country", 
					  "address_status", "payer_email", "payer_status", 
					  "payment_type", "subscr_date", "period1", "period2", 
					  "period3", "amount1", "amount2", "amount3", 
					  "recurring", "reattempt", "retry_at", "recur_times", 
					  "username", "password", "subscr_id", "option_name1", 
					  "option_selection1", "option_name2",
					  "option_selection2", "num_cart_items"
					);

// build insert statement
			while (list ($key, $value) = each ($_POST)) {
				if (in_array ($key, $paypal_vals)) {
					if (is_numeric($value)) {
						$addtosql .= " $key=$value,";
					} else {
						$newval = urlencode($value);
						$topost .= "&$key=$newval"; //used later in reposting
						$value = addslashes($value);
						$addtosql .= " $key='$value',";
					} //fi
				} //fi
				$entirepost .= "[$key]='$value',";
			} //wend

			$entirepost = addslashes($entirepost); // just in case..

			$addtosql = substr("$addtosql", 0, -1).";"; //chop trailing "," replace with ";"

			$sql1 = "
    INSERT INTO accounting_paypal
    SET confirm_date=now(), entirepost='$entirepost',  $addtosql";

			$res = mysql_query($sql1);
			$err = mysql_error();
			if ($err) die("$err :: $sql1");

// We could use this in a log, or to track which users have which payment.
			$this->paypal_id = mysql_insert_id();



		} // END SAVETODB

	function accept()
		{
			if ($_POST['payment_status'] == "Completed"
				|| $_POST['payment_status'] == "Pending")
			{
				// we have a successful transaction! 
				// update income here!				
				// get incomeid
				// split out the accountnumber, 
						//then the familyid/leadid/companyid
				//finally save to the appropriate tables
	
			} //fi     
			
		}  // END ACCEPT

} /// END IPNCLASS


# end of inner php code

?>

<!-- END IPN CLASS -->
