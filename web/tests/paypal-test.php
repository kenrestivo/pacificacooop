<?php

	require_once("shared.inc");

	print "<HTML>
		<HEAD>
			<TITLE>TESTING</TITLE>
		</HEAD>

		<BODY>

	";


	print " here i am <br>\n";

	printf("<FORM METHOD=POST ACTION='%s'>", 
					$_SERVER['PHP_SELF']);
	print "\n<table border=1>\n";
	//user popup (or display) stuff
	print "\n<tr>\n";
		print "\t<td>Select user name:\n</td>\n";

		print "\t<td>";
///here
		print "</td>\n";

	print "</tr>\n";

	//password stuff
	print "\n<tr>\n";
			print "<td>Please type in your new password here: </td>\n";
		print "\t<td><INPUT TYPE=text NAME='top[auth][pwd]'></td>\n";
	print "</tr>\n";


	print "\n</table>\n";


	printf("<INPUT TYPE=submit NAME='login' VALUE='%s'>",
		$type == 'confirm' || $type == 'both' ? 'Save New Password' : 'Log In');
	printf("<INPUT TYPE=submit NAME='logout' VALUE='Cancel'>");
    print "</FORM>";
	
	confessArray($_POST, "postvars");
	confessArray($GLOBALS, "globals");


?>
