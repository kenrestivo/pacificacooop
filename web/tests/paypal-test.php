<?php


	require_once("session.inc");
	sessionSetup();

	require_once("auth.inc");

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

	printf("\n<a href='index.php?%s'>test session-enabled link</a>\n", SID);


?>
