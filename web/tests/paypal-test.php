<?php


	// session stuff
	require_once("session.inc");
	sessionSetup();
	$auth = $_SESSION['auth'];

	require_once("shared.inc");

	//header stuff
	print "<HTML>
	<HEAD>
		<TITLE>Springfest Fundraising</TITLE>
	</HEAD>

	<BODY> 
	";

	//invalid or non-existent session
	if(!($auth && ($auth['state'] == 'loggedin'))){
		$pv = $HTTP_POST_VARS ? $HTTP_POST_VARS : $HTTP_GET_VARS;
		$auth = logIn($pv);
	}

	if($auth['state'] != 'loggedin'){
		user_error(sprintf("main(): session [%s] is not logged in", SID),
			E_USER_NOTICE);
		done();
	}

	//yay! i'm logged in! save it!
	$_SESSION['auth'] = $auth;

	$u = getUser($auth['uid']);
	user_error(sprintf("main(): LOGGED IN AS username [%s], familyname [%s], uid %d familyid %d sid [%s]", 
		$u['username'], $u['familyname'], $auth['uid'], $u['familyid'], SID), 
		E_USER_NOTICE);

	topNavigation($auth, $u);

	printf("\n<a href='index.php?%s'>test session-enabled link</a>\n", SID);

	done();


?>
