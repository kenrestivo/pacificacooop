<?php

	require_once("shared.inc");

	print "<HTML>
		<HEAD>
			<TITLE>TESTING</TITLE>
		</HEAD>

		<BODY>

	";


	print " here i am <br>\n";
	
/*
	if(NULL < "2004-01-02"){
		print "NULL is less<br>";
	}

	if("0000-00-00" < "2004-01-02"){
		print "0000 is less<br>";
	}

	if("2004-01-02" < "2004-01-02"){
		print "day later is less<br>";
	}
*/


$ar = array("2004-03-01", "2004-08-01", "2005-03-01", "2000-12-04");
foreach ($ar as $i){
	print "$i = " . findSchoolYear($i) . "<br>";
}
	print "TODAY = " . findSchoolYear() . "<br>";

	print "</body></html>";


?>
