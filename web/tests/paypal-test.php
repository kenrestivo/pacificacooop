<?php

	require_once("shared.inc");

	print "<HTML>
		<HEAD>
			<TITLE>TESTING</TITLE>
		</HEAD>

		<BODY>

	";


	print " here i am <br>\n";
	
function foobar($thing, $another = NULL)
{
	print "\n<br>$thing: ";
	if($another != NULL){
		print "YES [$another]";
	}
}


foobar("noval");
foobar("emptystring" , "");
foobar("string" , "fa");
foobar("num" , "1");

?>
