
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



require_once "HTML/QuickForm.php";
require_once "HTML/Table.php";
require_once "HTML/QuickForm/group.php";
require_once "paypal.php";



/// ADS
function ads()
{

	$prices_raw = array(
		"Back Page or Inside Front/Back Cover ($%s)" => 250,
		"Full Page ($%s)" => 150,
		"1/2 Page ($%s)" => 85,
		"1/4 Page ($%s)" => 50,
		"Business Card ($%s)" => 30
		);

	$form = new  paypalForm(  'Springfest Program Ad',  'adform', 
							  $_REQUEST['source']);
	$form->addElement($form->buildSelect('amount', $prices_raw));
	$form->addElement('submit', NULL, 'Buy Ad');
	$form->addGroup($line, null, null, "&nbsp;");
	$res .= $form->toHTML();
	return $res;
}

/// SPONSORTHIP
function sponsor()
{

	$prices_raw = array(
		"Angel ($%s)" => 1000,
		"Champion ($%s)" => 500,
		"Patron ($%s)" => 250,
		"Friend ($%s)" => 150,
		);

	$form = new paypalForm( 'Springfest Sponsorship', 'sponsorfrm',
							$_REQUEST['source']);
	$form->addElement($form->buildSelect('amount', $prices_raw));
	$form->addElement('submit', NULL, 
				  'Buy Sponsorship');
 	
	$res .= $form->toHTML();
	return $res;
}

/// DONATIONS
	
function donation()
{
	$form = new paypalForm('Springfest Cash Donation', 'donatefrm', 
						   $_REQUEST['source']);
	$form->addElement("text", "amount", "Donate amount:",
											 array('value' => "$100", 
												   'size' => 4));
	$form->addElement('submit', NULL, 'Donate');

	$res .= $form->toHTML();
	return $res;
}

/// TICKETS
function tickets()
{	
	 
	$form = new paypalForm('Springfest Event Tickets', 'ticketfrm', 
						   $_REQUEST['source']);
	$form->removeElement('quantity');
	$form->addElement("hidden", "undefined_quantity", "1");
	$form->addElement("hidden", "amount", "25");
 	$form->addElement("text", "quantity", 
 											 "Number of tickets:", 
 											 array('value' => 2, 
 												   'size' => 4));
	
	$form->addElement('submit', NULL, 
									"Buy Tickets"		 );
	$res .= $form->toHTML();
	return $res;

}

///////////////////////
///////// MAIN

print '<HTML> 
<HEAD> 
		<link rel=stylesheet href="main.css" title=main>
		<TITLE>Donate for Springfest</TITLE> 
</HEAD> 
<BODY> 

		<h2>Pacifica Co-Op Nursery School Springfest</h2> ';

// TODO: put springfest date into the year settings, and fish it out of the database here!

print "<p>On <em><b>Saturday, March 19th</b></em>, 6:30 to 10:00pm, you are invited to
attend the Pacifica Co-op Nursery School&#39;s annual
 Springfest at the Pacifica Community Center (540 Crespi Drive, Pacifica). This
wonderful evening will include silent and live auctions, wine tasting, hearty
appetizers, live music, and dancing. Proceeds benefit the Pacifica Co-op Nursery
 School.</p><br />
<p>The event is <em><b>fabulous</b></em> and definitely worth finding a babysitter!</p><br />
<p>Tickets are available in advance for $25 or at the door for $30. Must be 21 or over to attend. For tickets and information,
please call <strong>(650) 557-9257 or <a href=\"#tickets\">click here to order online with your credit card</a>.</strong> </p><br />";

print "<p>We are looking for generous individuals and businesses who want to support the work of the coop. There are many benefits to becoming a sponsor:</p>";

print	"<h3>Angel Contribution of $1,000.00 or more:</h3>
<li>Site link on Pacifica Co-Op Nursery website 
<li>Sponsor logo on Pacifica Co-Op Nursery website 
<li>Half page ad in the Springfest program 
<li>Four tickets for Springfest 2005 
<li>Individual sponsor banner at the event 

 <h3>Patron Contribution of $250.00 to $499.00 </h3>
<li>Mention on Pacifica Co-Op Nursery website
<li>Business card size ad in the Springfest program 
<li>Two tickets for Springfest 2005


 <h3>Champion Contribution of $500.00 to $999.00 </h3>
<li>Site link on Pacifica Co-Op Nursery website 
<li>Quarter page ad in the Springfest program 
<li>Two tickets for Springfest 2005 
<li>Listing on Champions' banner at the event

 <h3>Friend Contribution of $150.00 to $249.00 </h3>
<li>Mention on Pacifica Co-Op Nursery website 
<li>Mention in Springfest 2005 program 
<li>Two tickets for Springfest 2005";

print sponsor();
print "<hr>";



print "<h3>Advertise in the Springfest program</h3>";
print  ads();
print "<hr>";



print "<h3>Donate cash of any amount</h3>";
print donation();
print "<hr>";


print '<a name="tickets">';
print "<h3>Purchase tickets to Springfest</h3>";
print "<p>Event tickets are $25 each</p>";
print tickets();
print "<hr>";




print "</body></html>";


// keep below
?>
<!-- DONATIONS -->




