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

require_once("roster.inc");

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
	
	function coopPage($debug = false)
		{
			$this->debug = $debug;
		}

	// for use with the old, non-object-oriented, homegrown auth/dispatcher
	function createLegacy($auth)
		{
			$this->auth = $auth;
			$this->userStruct =  getUser($auth['uid']);
		}
 
	function pageTop()
		{

			print '<HTML>
				<HEAD>
						<link rel=stylesheet href="main.css" title=main>
							<TITLE>Data Entry</TITLE>
				</HEAD>
				<BODY>
				<h2>Pacifica Co-Op Nursery School Data Entry</h2>
				';


				$this->confessArray($_REQUEST, "test REQUEST");
				$this->confessArray($_SESSION, "test SESSION");
				$this->confessArray($_SERVER, "test SERVER");


			warnDev();

			user_error("CoopPage.php: ------- NEW PAGE --------", 
					   E_USER_NOTICE);


			$this->auth = logIn($_REQUEST);


			if($this->auth['state'] != 'loggedin'){
				done();
			}



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

	function confessArray($array, $message)
		{
			if($this->debug < 1){
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
	//					"page.php")
	// all of which are optional
	// without any args, returns just coop session var for use in Header()
	function selfURL($value = false, $inside = false, $base = false, $popup = false)
		{
			if(!$base){
				$base = $_SERVER['PHP_SELF'];
			}
			 if(($pos = strpos($base, '?')) !== false) {
                $base = substr($base, 0, $pos);
            }
			 if($value){
				 $res .= '<p><a href="';
				 if($popup){
					 $res .= "javascript:popUp('";
				 }
			 }
			 if($inside){
 				 $res .= sprintf("%s?%s%s",
								$base, $inside,
								SID ? "&" . SID  : "");
			 } else {
				 $res .= $base .  SID ? "?" . SID  : "";
			 }
			 if($value){
				 if($popup){
					 $res .= "')";
				 }
				 $res .= sprintf('">%s</a></p>', $value);
			 }
			 return $res;
		}

	

} // END COOP PAGE CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP PAGE -->


