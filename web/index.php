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

	print "<HTML>
		<HEAD>
			<TITLE>Data Entry</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School Data Entry</h2>
	";

	$pv = $HTTP_POST_VARS ? $HTTP_POST_VARS : $HTTP_GET_VARS;


	$auth = logIn($pv);

	$u = getUser($auth['uid']);

	print "<hr>\n";


	if($auth['state'] != 'loggedin'){
		done();
	}
	
	print "<p>Please choose an action</p>";

	print "<table border=1>";
	tdArray( array ("Description", "Summary", "Actions"), 'align=center');

	//auction items
	print "<tr>";
	$p = getAuthLevel($auth, 'auction');
	print "<td>Sprintfest Auction Donation Items</td><td>";
	auctionSummary($u['familyid']);
	print "</td><td align=center>";
	auctionAction($auth, $p, $u, $u['familyid']);
	print "</td><tr>";
	

	print "</table>";


	done();
?>
<!-- END INDEX -->
