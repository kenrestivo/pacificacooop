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
	print "<tr>";
	$p = getAuthLevel($auth, 'auction');
	$admin = $p['grouplevel'] >= ACCESS_EDIT ? 1 : 0;
	print "<td>Springfest Auction Donation Items</td><td>";
	$s = auctionSummary($u['familyid']);
	print "</td><td align=center>";
	actionButtons($auth, $p, $u, $u['familyid'], 'auction', 
			'auction.php', $s + $admin);
	print "</td><tr>";
	
	//invitations
	print "<tr>";
	$p = getAuthLevel($auth, 'auction');
	$admin = $p['grouplevel'] >= ACCESS_EDIT ? 1 : 0;
	print "<td>Springfest Invitation Contacts</td><td>";
	$s = nameSummary($u['familyid']);
	print "</td><td align=center>";
	actionButtons($auth, $p, $u, $u['familyid'], 'invitations', 
			'10names.php', $s + $admin);
	print "</td><tr>";

	//money items
	print "<tr>";
	$p = getAuthLevel($auth, 'money');
	$admin = $p['grouplevel'] >= ACCESS_EDIT ? 1 : 0;
	print "<td>Springfest Fees and Cash Donations</td><td>";
	$s = incomeSummary($u['familyid']);
	print "</td><td align=center>";
	actionButtons($auth, $p, $u, $u['familyid'], 'money', 
		'money.php' , $s + $admin);
	print "</td><tr>";

	print "</table>";

	familyDetail($u['familyid']);

	done();
?>
<!-- END INDEX -->
