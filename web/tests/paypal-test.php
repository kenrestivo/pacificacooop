<?php

	sessionSetup();
	print "<HTML>
		<HEAD>
			<TITLE>TESTING</TITLE>
		</HEAD>

		<BODY>

	";

	setSession();

	print " here i am \n";
	
	printSession();

	print "</body></html>";

function
setSession()
{

	$_SESSION['test'] = "i am testing";


}

function
printSession()
{

	print_r($_SESSION);

}

/******************
	SESSIONSETUP
******************/
function
sessionSetup()
{
    // Stop adding SID to URLs
    ini_set('session.use_trans_sid', 0);

    // How to store data
    ini_set('session.serialize_handler', 'php');

    // cookies suck.
    ini_set('session.use_cookies', 0);

    // Name of our cookie
    ini_set('session.name', 'coop');

    // Lifetime of our cookie
    //TODO ini_set('session.cookie_lifetime', $lifetime);
    
    // Garbage collection
    ini_set('session.gc_probability', 1);

    // Inactivity timeout for user sessions
    //TODO ini_set('session.gc_maxlifetime', $mins * 60);

    // Auto-start session XXX do i WANT this??!
    ini_set('session.auto_start', 1);

    /* Session handlers 
    ini_set('session.save_handler', 'user');
    session_set_save_handler("openSess",
                             "closeSess",
                             "readSess",
                             "writeSess",
                             "destroySess",
                             "GCsess");
	*/

	session_start();

	//user_error("session has been set up", E_USER_NOTICE);

    return true;
} /* END SESSIONSETUP */

?>
