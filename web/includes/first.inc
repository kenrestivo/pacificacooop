<?php

#	<!-- $Id$ -->
#	the vital setup stuff that ALL files MUST have

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


//TODO: try encapsulating this in a setupDB() function. i hate naked code

#conditionally include a file with the dbhost name
#this is a separate file so that i can CVS this code without having 
#all kinds of spurious commits whenever i move it from one host to another
#for testing and such.
$dbfile ="dbhost.inc" ;

if(is_file($dbfile)){
	include($dbfile);
}

$dbhost = $dbhost ? $dbhost : "bc";
$coop_sendto = $coop_sendto ? $coop_sendto : 'ken@restivo.org';

$dbuser = "springfest";
$dbpwd = "92xPi9";


/*  handle dev stuff. 
	special-case hack around bc, where i don't use a -dev database 
*/
$parth = pathinfo($_SERVER['SCRIPT_FILENAME']);
$dir = $parth['dirname'] ;
if(preg_match('/-dev/', $dir) > 0  && $dbhost != "bc"){
	$dbname = "coop_dev";
} else {
	$dbname = "coop";
}


#connect to the database
$dbh = mysql_connect($dbhost,$dbuser,$dbpwd);
if(!$dbh){
	user_error("can't connect to $dbhost as $dbuser\n", 
		E_USER_ERROR);
	exit(1);
}

register_shutdown_function(byebye);
mysql_select_db($dbname) or die ("can't use $dbname! ack!\n");

setupErrors();
sessionSetup();

user_error("first.inc: everything should be set up", E_USER_NOTICE);	

// end of main code

######################################################




/******************
	SETUPERRORS
******************/
function
setupErrors()
{
	global $dbhost;

	// global error stuff that we'll need
	$errlevel = E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR ;
	if($dbhost == 'bc'){ // can't use this crap on nfsn
		$errlevel = $errlevel | E_USER_WARNING | E_USER_NOTICE;
	}
	error_reporting($errlevel );
	//error_reporting(0);

	/* well, try it 
	$olderr = set_error_handler("errorHandler");
	*/

}/* END SETUPERRORS */


/******************
	ERRORHANDLER
	function lifted from php docs
******************/
function
errorHandler($errno, $msg, $filename, $linenum, $vars)
{
	$mailto = "ken@restivo.org";

    // timestamp for the error entry  
    $dt = date("Y-m-d H:i:s (T)");

    // for friendly happy error messages
    $errortype = array (                                                        
                1   =>  "Error",   
                2   =>  "Warning",
                4   =>  "Parsing Error",  
                8   =>  "Notice",      
                16  =>  "Core Error",    
                32  =>  "Core Warning",     
                64  =>  "Compile Error",
                128 =>  "Compile Warning",      
                256 =>  "User Error", 
                512 =>  "User Warning",    
                1024=>  "User Notice"  
	);

    // set of errors for which a var trace will be saved 
	//TODO a way to turn NOTICE on and off at runtime, for debugging
	$logthese = array (E_USER_NOTICE, E_USER_ERROR, E_USER_WARNING, 
			E_ERROR, E_WARNING );
    if (in_array($errno, $logthese)){
		$err = sprintf("%s: %s in filename %s at %s ",
			$errortype[$errno], $msg, $filename, $linenum
		);
							
		$user_errors = array(E_USER_NOTICE, E_USER_ERROR, E_USER_WARNING ); 
		if (in_array($errno, $user_errors))
			$err .= sprintf("trace: <%s>\n",
						confessArray($vars,"Variables"));
		$err .= "\n";
		//error_log($err, 3, "../logs/debug.log");    
		error_log($err, 0);    
	}

    /* e-mail me if there is a critical user error
    if ($errno == E_USER_ERROR)     
        mail($mailto,"Critical User Error",$err);
	*/

}/* END ERRORHANDLER */


	function byebye()
	{
		global $dbh;

			mysql_close($dbh);
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

    // Garbage collection
    ini_set('session.gc_probability', 1);

    // Inactivity timeout for user sessions
    //TODO ini_set('session.gc_maxlifetime', $mins * 60);

    /* Session handlers */
    ini_set('session.save_handler', 'user');
    session_set_save_handler("openSess",
                             "closeSess",
                             "readSess",
                             "writeSess",
                             "destroySess",
                             "GCsess");
	session_start();

	user_error("session has been set up", E_USER_NOTICE);

    return true;
} /* END SESSIONSETUP */




/******************
	OPENSESS
	a no-op with db
******************/
function
openSess($path, $name)
{
	return true;
}/* END  */

/******************
	CLOSESESS
	a no-op with db
******************/
function
closeSess()
{
	return true;
}/* END  */



/******************
	READSESS
	inputs: session id
	returns: the string'ed serialised vars
******************/
function
readSess($sessid)
{
	user_error("readSess: reading session id for [$sessid]", 
		E_USER_NOTICE);

	$vars = '';

	#DO THE QUERY
	$q = "select vars from session_info where session_id = '$sessid'";
	$listq = mysql_query($q);
	$err = mysql_error();
	if($err){
		user_error("readSess(): [$q]: $err", E_USER_ERROR);
	}
	while($row = mysql_fetch_array($listq)){
		$vars = $row['vars'];
	}
	return($vars);
}/* END READSESS */



/******************
	WRITESESS
	writes the vars out to the db
	inputs; sesion id, serialised vars
	returns: true on success, false otherwise
******************/
function
writeSess($sessid, $vars)
{
	if(!($vars && $sessid)){
		user_error("writeSess: empty session [$sessid]  vars [$vars]", 
			E_USER_NOTICE);
		return false;
	}

	user_error("writeSess: writing session [$sessid] vars [$vars]", 
		E_USER_NOTICE);
	$query = sprintf("replace into session_info 
			set vars = '%s' , session_id = '%s'",
			 mysql_escape_string($vars), $sessid);
	if(mysql_query($query)){
		$rows = mysql_affected_rows();
	}
	$err = mysql_error();
	if($err){
		user_error("writeSess(): [$query]: $err", E_USER_ERROR);
	}

	/* hack. nothign affected, so it must be new.
	if($rows){
		return true;
	}

	user_error("writeSess: FIRST TIME for new session [$sessid] [$vars]", 
		E_USER_NOTICE);
	$query = sprintf("insert into session_info 
			set vars = '%s', session_id = '%s', entered = now()",
			 mysql_escape_string($vars), $sessid);
	if(mysql_query($query)){
		$rows = mysql_affected_rows();
	}
	$err = mysql_error();
	if($err){
		user_error("writeSess(): [$query]: $err", E_USER_ERROR);
	}
	*/

	return true;
}/* END WRITESESS  */



/******************
	DESTROYSESS
	nukes the session from the db
	inputs; sesion id
	returns: true on success, false otherwise
******************/
function
destroySess($sessid)
{
	user_error("destroySess: nuking session id [$sessid]", 
		E_USER_NOTICE);
	$query = sprintf("delete from session_info 
					where session_id = '%s' LIMIT 1", $sessid);
	if(mysql_query($query)){
		$rows = mysql_affected_rows();
	}
	$err = mysql_error();
	if($err){
		user_error("writeSess(): [$query]: $err", E_USER_ERROR);
	}
	return true;
}/* END DELETESESS */



/******************
	GCSESS
	garbage collect: remove any old session vars
		which have expired
	inputs: maximum lifetime to check
	returns: true on success, false otherwise
******************/
function
GCsess($maxlifetime)
{
	user_error("GCsess: garbage collecting sessions older than [$maxlifetime]", 
		E_USER_NOTICE);
	//TODO: haven't attacked this yet. may have to do ugly date calculations
}/* END  */


?>
