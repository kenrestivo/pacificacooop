<?php

	print "<HTML>
		<HEAD>
			<TITLE>TESTING</TITLE>
		</HEAD>

		<BODY>

	";


	print " here i am <br>\n";
	
	if(NULL < "2004-01-02"){
		print "NULL is less<br>";
	}

	if("0000-00-00" < "2004-01-02"){
		print "0000 is less<br>";
	}

	if("2004-01-02" < "2004-01-02"){
		print "day later is less<br>";
	}



	print "</body></html>";


?>
