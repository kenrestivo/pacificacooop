<!-- $Id$ -->
<?php

	#  Copyright (C) 2003  ken restivo <ken@restivo.org>
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

	require_once("auth.inc");
	require_once("auctionfuncs.inc");
	require_once("financefuncts.inc");
	require_once("roster.inc");
	require_once("10names.inc");
	require_once("insurancefuncs.inc");
	require_once("calendarfuncs.inc");
	require_once("adminfuncs.inc");
	require_once("solicitfuncs.inc");

	print "<HTML>
		<HEAD>
			<TITLE>Data Entry</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School Data Entry</h2>
	";

	$pv = $HTTP_POST_VARS ? $HTTP_POST_VARS : $HTTP_GET_VARS;


	$auth = logIn($pv);

	if($auth['state'] != 'loggedin'){
		done();
	}

	//OK, i am logged in!
	
	$u = getUser($auth['uid']);

	topNavigation($auth, $u);
	print "<hr>\n";

	print "<p>Please choose an action:</p>";

	print "<table border=1>";
	tdArray( array ("Description", "Summary", "Actions"), 'align=center');
	/* hmm. i see a repetitive pattern here. and... wherever i see a pattern, 
		i can't resist abstracting it out into a libraray function. 
		so... guess what's gonna be next here 
	*/

	//auction items
	showMenuRow($auth, $u, 'Springfest Auction Donation Items', 
		'auctionSummary', 'auction', 'auction.php');
	
	//invitations
	showMenuRow($auth, $u, 'Springfest Invitation Contacts', 
		'nameSummary', 'invitations', '10names.php');

	//money items
	showMenuRow($auth, $u, 'Springfest Fees and Cash Donations', 
		'incomeSummary', 'money', 'money.php');

	//solicitation items
	showMenuRow($auth, $u, 'Springfest Solicitation', 
		'solicitSummary', 'solicitation', 'solicitation.php');

	/* insurance */
	showMenuRow($auth, $u, 'Insurance and Drivers Licence Information', 
		'insuranceSummary', 'insurance', 'insurance.php');

	/* calendar */
	showMenuRow($auth, $u, 'Calendar of Events', 
		'calSummary', 'calendar', 'calendar.php');

	/* admin 
	XXX can't use standard showMenuRow? 
		it uses FAMILYID, but admin wants USERID
	*/
	showMenuRow($auth, $u, 'User Administration', 
		'adminSummary', 'user', 'admin.php');

	print "</table>";

	familyDetail($u['familyid']);

	done();
?>
<!-- END INDEX -->
