<?php 

//$Id$

/*
	Copyright (C) 2004  ken restivo <ken@restivo.org>
	 
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

require_once("first.inc");
require_once("shared.inc");
require_once("auth.inc");
require_once('Mail.php');


function confessObj($obj, $text)
{

    print"<pre>\n======== $text ============\n";
    print htmlentities(print_r($obj, 1));
    print "</pre>";
 
}

//////////////////////////////////////////
/////////////////////// COOP CLASS
class coopPage
{
	var $auth;
	var $debug;
	var $indexed_all; 			// legacy stuff. ALL of the callbacks
	var $userStruct;				// cache of legacy info ($u)
	var $bufferedOutput;			// to store buffered output, for QFC
	
	function coopPage($debug = false)
		{
			$this->debug = $debug;
			PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 
								   array(&$this, 'kensPEARErrorHandler'));
		}

	// for use with the old, non-object-oriented, homegrown auth/dispatcher
	function createLegacy($auth)
		{
			$this->auth = $auth;
			$this->userStruct =  getUser($auth['uid']);
		}


	// NOTE! prints directly to screen, doesn't return output!
	function header($title = 'Data Entry', 
					$heading = 'Pacifica Co-Op Nursery School Data Entry')
		{
			global $metalinks; // from first.inc. bah.
			global $doctype; // from first.inc. bah.
			printf('%s <HTML lang="en">
		<HEAD> %s
			<TITLE>%s</TITLE>
		</HEAD>

		<BODY>

		<div id="header">
				<h2>%s</h2>',
				   $doctype, $metalinks, $title, $heading);
			
			
			$this->debugCrap();
			warnDev();
			
			user_error("CoopPage.php: ------- NEW PAGE --------", 
					   E_USER_NOTICE);

		}

	function debugCrap()
	{
		$this->confessArray($_REQUEST, "test REQUEST");
		$this->confessArray($_SESSION, "test SESSION (prior to request being processed)");
		$this->confessArray($_SERVER, "test SERVER", 4);
				
	}

 
	function pageTop()
		{
			ob_start();
			$this->header();
		
			$this->auth = logIn($_REQUEST);

			$this->confessArray($this->auth, 'auth -- post login');

			if($this->auth['state'] == 'loggedin'){
				// pretty sure i need this here, in case i'm not using legacy
				$this->userStruct =  getUser($this->auth['uid']);
			} else{
				// show the login, then be done with this
				ob_end_flush();
				done();
			}

			$output = ob_get_clean();
			ob_end_flush();

			return $output;


		}


	// grab the legacy includes and index them
	function indexEverything($everything)
		{
			foreach ($everything as $thang => $val){
				//XXX i can't grab the fields, because they're globals. globals SUCK!
				//global $$val['fields'];
				//print $val['fields'];
				//$this->confessArray($val['fields'], 'fields');
				$val['fields'] = $$val['fields'];
				$indexed_everything[$val['page']] = $val;
	
			}
			//$this->confessArray($indexed_everything, 'indexedeverythinag');
			return $indexed_everything;
		} 

	function confessArray($array, $message, $level = 1)
		{
			if($this->debug < $level){
				return;
			}
			confessArray($array, $message);
		}


	// XXX broken. it needs to instantiate views first
	function engine(){
		if($_REQUEST['tables']){
			$_SESSION['tables'] = $this->mergeArrays($_SESSION['tables'], 
													 $_REQUEST['tables']);
		}
		//confessArray($tabarr, "tables");
		foreach($_SESSION['tables'] as $table => $vals){
			$this->setup($table); // XXX botcherunio. needs to instantiate views.
				//	print_r($cp);
			// OK copy my dispatcher logic over now
			switch($vals['action']){
			case 'list':
				print $this->editAddTable();
				break;
			case 'detail':
				print $this->detailForm($vals['id']);
				break;
			}
		}

	} /// end engine
	
	function mergeArrays($array, $overrides, $level = 0)
		{
			$this->confessArray($array, "BEFORE merge: level $level");

			foreach($overrides as $key => $val){
				if(array_key_exists($key, $array)){
					if(is_array($val)){
						$array[$key] = 
							$this->mergeArrays($array[$key], $val, $level +1);
					} else {
						$array[$key] = $val;
					}
					
				} else {
					$array[$key] = $val;
				}
			}
						

			$this->confessArray($array, "AFTER  merge, level $level");
		   			return $array;
		}


	// TODO: some nifty way to get session vars outta there
	// fishes the tables out of a request or session
	function requestOrSession($itemName){
	}
	


	// USAGE: selfURL(
	//					"text to display",
	//					"var=value&morevar=morevalue",
	//							or an array of pairs
	//					"page.php")
	// all of which are optional
	// without any args, returns just coop session var for use in Header()
	function selfURL($value = false, $inside = false, 
					 $base = false, $popup = false)
		{
			if(!$base){
				$base = $_SERVER['PHP_SELF'];
			}
			 if(($pos = strpos($base, '?')) !== false) {
				 $base = substr($base, 0, $pos);
			 }
			 if($value){
				 $res .= '<p><a href="';
			 }

			 if(!$popup){
				 $res .= $this->formatURLVars($base, $inside);
			 }

			 if($value){
				 $res .= '" ';
				 if($popup){
					 $res .= sprintf('onClick="popUp(\'%s\')"',
									 $this->formatURLVars($base, $inside));
				 }
				 $res .= sprintf('>%s</a></p>', 
								 $value);
			 }
			 return $res;
		}


	function formatURLVars($base, $inside = false)
		{
			
			if($inside){
				if(is_array($inside)){
					foreach($inside as $var => $val){
						$pairs[] = sprintf('%s=%s', $var, 
										   htmlentities($val));
					}
					if(SID){
						$pairs[] = SID;
					}
					$inside = implode('&', $pairs);
				} 
				// NOTE i can't call htmlentities on string $insides
				// because i don't want to fuck up the &'s
				$res .= sprintf("%s?%s%s",
							   $base, $inside,
							   SID ? "&" . SID  : "");
			} else {
				$res .= $base .  SID ? "?" . SID  : "";
			}
	
			return htmlentities($res);
		}


	function mailError($subject, $body)
		{

			$body .= sprintf("--- BACKTRACE--\n%s", 
							 print_r(debug_backtrace(), true));


			// TODO: blow away the global from legacy, use var


			global $coop_sendto;
			$to =  $coop_sendto['email_address'];
			
			$headers['From']    = 'bugreport@pacificacoop.org';
			$headers['To']      = 	$to;
			$headers['Subject'] = $subject;
			
			$mail_object =& Mail::factory('smtp', $params);
			
			$mail_object->send($to, 
							   $headers, 
							   $body);
			

		}	

	function fixURL($val)
		{
			// hack around fuckedup urls
			// the RIGHT way to do this is to validate at input time
			
			if(!preg_match('/^http:\/\/.+/', $val, $matches)){
				return sprintf("http://%s", $val);
			} 

			return $val;

		}

	function kensPEARErrorHandler(&$obj)
		{
			if(devSite()){
				confessObj($obj, 'pear error. bummer.');
				exit(1);
			}
			$this->mailError('PEAR error on live site!',
							 print_r($this));
			exit(1);
		}



} // END COOP PAGE CLASS


////KEEP EVERTHANG BELOW

?>