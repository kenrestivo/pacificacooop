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
			<TITLE>Springfest Fundraising</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School</h2>
	";

//	confessArray($HTTP_POST_VARS, "vars");

	$auth = logIn($HTTP_POST_VARS['auth']);

	print "index.php. logIn() returns with:";
//	confessArray($auth, "afterlogin");
	error_log("   ");

	print "</BODY> </HTML>"

?>
<!-- END INDEX -->
