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

	//confessArray($auth, "index.php. login() returns with");
	error_log("   ");

	if($auth['state'] != 'loggedin'){
		done();
	}
	
	print "<p>This is the first day of the rest of your life!</p>";

/*
	show friendlyHappyWelcome from 10names
	show parents and kids in this family, 
		and some roster information: what session (AM/PM), etc.
	menu of choices
		call auth.inc:checkAuthLevel() before displaying each choice
		- enter 10names 
		- enter 3x5 cards 
		- edit roster information (phone, email, name spelling, etc)
		- show insurance information
		- enter checks (if authorised for "money" realm)
*/

	done();
?>
<!-- END INDEX -->
