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

	print "<HTML>
		<HEAD>
			<TITLE>Data Entry</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School Data Entry</h2>
	";

	$pv = $HTTP_POST_VARS ? $HTTP_POST_VARS : $HTTP_GET_VARS;

	//confessArray($HTTP_POST_VARS, "vars");

	$auth = logIn($pv);

	print "<hr>\n";

	//confessArray($auth, "index.php. login() returns with");
	trigger_error("comin down the mountain....", E_USER_NOTICE);

	if($auth['state'] != 'loggedin'){
		done();
	}
	
	print "<p>Please choose an action</p>";

	// TODO abstract this out. it needs to be a utility function.
	print "<table border=1>";

	//names
	printf("<FORM METHOD=POST ACTION='%s'>", 
					"10names.php");
	thruAuth($auth);	
	tdArray(array (
					"SpringFest Invitation Contacts",
					"You have entered XXX names so far", 
					sprintf("<INPUT TYPE=submit NAME='%s' VALUE='%s'>", 
						'view', 'View') // eventually decide this via auth
				)
	);
	print "</FORM>";


	// auction items
	printf("<FORM METHOD=POST ACTION='%s'>", 
					"auction.php");
	thruAuth($auth);	
	tdArray(array (
					"SpringFest Auction Donation Items",
					"You have donated XXX items so far", 
					sprintf("<INPUT TYPE=submit NAME='%s' VALUE='%s'>", 
						'view', 'View') // eventually decide this via auth
				)
	);
	print "</FORM>";

	//payments, etc
	printf("<FORM METHOD=POST ACTION='%s'>", 
					"money.php");
	thruAuth($auth);	
	tdArray(array (
					"Payments", 
					"You have paid xxx dollars so far", 
					sprintf("<INPUT TYPE=submit NAME='%s' VALUE='%s'>", 
						'view', 'View') // eventually decide this via auth
				)
	);
	print "</FORM>";


	//rasta
	printf("<FORM METHOD=POST ACTION='%s'>", 
					"roster.php");
	thruAuth($auth);	
	tdArray(array (
					"Roster Information",
					"You have paid xxx dollars so far", 
					sprintf("<INPUT TYPE=submit NAME='%s' VALUE='%s'>", 
						'view', 'View') // eventually decide this via auth
				)
	);
	print "</FORM>";

	//insurance
	printf("<FORM METHOD=POST ACTION='%s'>", 
					"insurance.php");
	thruAuth($auth);	
	tdArray(array (
					"Auto Insurance Information",
					"Your insurance is current/expired", 
					sprintf("<INPUT TYPE=submit NAME='%s' VALUE='%s'>", 
						'view', 'View') // eventually decide this via auth
				)
	);
	print "</FORM>";

	print "</table>";


	done();
?>
<!-- END INDEX -->
