<?php

/*
	<!-- $Id$ -->
	the vital setup stuff that ALL files MUST have

  Copyright (C) 2003  ken restivo <ken@restivo.org>
 
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
 
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details. 
 
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

//portions lifted from postnuke
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html



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
	NOT USED (right now anwyay)
******************/
function
errorHandler($errno, $msg, $filename, $linenum, $vars)
{
	global $coop_sendto;
	$mailto = $coop_sendto['email_address'];

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


/******************
	SESSIONSETUP
******************/
function
sessionSetup()
{

	user_error("sessionSetup(): setting up session", E_USER_NOTICE);

	/* magic quotes aren't magic, they're evil! turn them off!!
	ini_set('magic_quotes_gpc', 0);
	*/
	ini_set('magic_quotes_runtime', 0);

	if(get_magic_quotes_gpc() || get_magic_quotes_runtime()){
		user_error("You CANNOT use this code with magic quotes on. Administrator needs to set this in .htaccess or php.ini. Sorry.", E_USER_ERROR);
	}
	// Stop adding SID to URLs. annoying and not all ISPs allow it
	ini_set('session.use_trans_sid', 0);

	// How to store data
	ini_set('session.serialize_handler', 'php');

	/* Name of our var TODO: make this a constant! use it EVERWHERE!
	ini_set('session.name', 'coop'); */

	// Garbage collection
	ini_set('session.gc_probability', 1);

	// Inactivity timeout for user sessions
	//TODO ini_set('session.gc_maxlifetime', $mins * 60);

	/* Session handlers */	
	session_set_save_handler("openSess",
								"closeSess",
								"readSess",
								"writeSess",
								"destroySess",
								"GCsess");
	session_name("coop");
	session_start();
	user_error(sprintf("started session with id [%s]", session_id()), 
			E_USER_NOTICE);

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

	$vars = '';

	if($sessid == ""){
		user_error("readSess: called with null session id!!", 
			E_USER_NOTICE);
		return($vars);
	}
	user_error("readSess: reading session id for [$sessid]", 
		E_USER_NOTICE);


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
	/* user_error("readSess: read session [$sessid]: vars [$vars]", 
		E_USER_NOTICE);
	*/
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
		user_error(sprintf("writeSess: empty vars [%s] or sessionid [%s] or %s ", $vars, $sessid, session_id()), 
			E_USER_WARNING);
		return(false);
	}
	
	$auth =& $_SESSION['auth']; //temp
	$lastuid =& $_SESSION['lastuid']; //temp
	$ip = getIpAddr();

	user_error(sprintf("writeSess: writing session [%s] vars [%s] uid %d ip [%s]",
		$sessid, $vars, $auth['uid'], $ip), E_USER_NOTICE);

	//TODO replace is WRONG. use update... and handle INSERT if new.
	$query = sprintf("replace into session_info 
			set vars = '%s' , session_id = '%s', user_id = %d, ip_addr = '%s'",
			 mysql_escape_string($vars), $sessid, 
                     $auth['uid'] ? $auth['uid'] : $lastuid, 
                     $ip);
	if(mysql_query($query)){
		$rows = mysql_affected_rows();
	}
	$err = mysql_error();
	if($err){
		user_error("writeSess(): [$query]: $err", E_USER_ERROR);
	}

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
	/*TODO: haven't attacked this yet. may have to do ugly date calculations
	 be sure in any case to delete any sessions older than just-recent 
		that have a state of 'needsomething' or 'loggedout', etc.
	*/
}/* END  */



/******************
	SETUPDB
        if $urlonly, just return the url, don't make the connection
        hack to glue my crufty old crap to dbobject
******************/
function
setupDB($urlonly = false)
{

	global $dbh;

	#conditionally include a file with the dbhost name
	#this is a separate file so that i can CVS this code without having 
	#all kinds of spurious commits whenever i move it from one host to another
	#for testing and such.
	$dbfile ="dbhost.inc" ;

	if(is_file($dbfile)){
		include($dbfile);
		global $dbhost;
		global $coop_sendto;
		global $dbuser;
		global $dbpwd;
	}


	$dbhost = $dbhost ? $dbhost : "bc";
	if(!$coop_sendto){
		$coop_sendto = array(
						'name' => 'ken',
						'email_address' => 'ken@restivo.org',
						'phone' => '650-355-1317'
				);
	}


	if(!($dbuser && $dbpwd && $dbhost)){
		user_error("ack! db user/host/pwd globals not defined", E_USER_ERROR);
	} 
	/* XXX MAJOR SECURITY HOLE! DO NOT DO THIS ON PRODUCTION CODE!
	else {
		user_error("setupDB(): connecting to $dbhost as $dbuser with $dbpwd", 
			E_USER_NOTICE);
	}
	*/

	/*  handle dev stuff. 
		special-case hack around bc, where i don't use a -dev database 
	*/
	$parth = pathinfo($_SERVER['SCRIPT_FILENAME']);
	$dir = $parth['dirname'] ;
	if(preg_match('/-dev/', $dir) > 0  && $dbhost != "bc" && 
				$dbhost != 'localhost'){
		$dbname = "coop_dev";
	} else {
		$dbname = "coop";
	}

    if(!$urlonly && !$dbh){
        //connect to the database
        $dbh = mysql_connect($dbhost,$dbuser,$dbpwd);
        if(!$dbh){
            user_error("can't connect to $dbhost as $dbuser\n", 
                       E_USER_ERROR);
            exit(1);
        }

        mysql_select_db($dbname) or die ("can't use $dbname! ack!\n");

    }
    
    global $dburl; 
    $dburl = sprintf("mysql://%s:%s@%s/%s",
                     $dbuser, $dbpwd, $dbhost, $dbname);
    
    return $dburl;

}/* END  SETUPDB */



/******************
	GETIPADDDR
	lifted verbatim from PostNuke
******************/
function
getIpAddr()
{
    $ipaddr = $HTTP_SERVER_VARS['REMOTE_ADDR'];

    if(empty($ipaddr)) {
        $ipaddr = getenv('REMOTE_ADDR');
    }

    if(!empty($HTTP_SERVER_VARS['HTTP_CLIENT_IP'])) {
        $ipaddr = $HTTP_SERVER_VARS['HTTP_CLIENT_IP'];
    }

    $tmpipaddr = getenv('HTTP_CLIENT_IP');
    if(!empty($tmpipaddr)) {
        $ipaddr = $tmpipaddr;
    }

    if(!empty($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) {
        $ipaddr = preg_replace('/,.*/', '', 
			$HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR']);
    }

    $tmpipaddr = getenv('HTTP_X_FORWARDED_FOR');
    if(!empty($tmpipaddr)) {
        $ipaddr = preg_replace('/,.*/', '', $tmpipaddr);
    }

	return $ipaddr;

}/* END GETIPADDDR */




?>
