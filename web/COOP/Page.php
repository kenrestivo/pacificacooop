<?php 

//$Id$

/*
	Copyright (C) 2004-2005  ken restivo <ken@restivo.org>
	 
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
require_once("auth.inc");
require_once('Mail.php');
require_once 'HTML/Table.php';


function confessObj($obj, $text, $outofband = true)
{

    $res = sprintf("<pre>\n======== $text ============\n%s</pre>",
				   htmlentities(print_r($obj, 1)));

	if($outofband){
		dump($res);
	} else {
		print $res;
	}
}

function dump($data, $close = false)
{

    if(!devSite()){
        return;
    }
    
    //XXX DAMMIT!  NFSN does *not* like me doing this
	// the getcwd is kind of redundant
	$fname = sprintf("%s/logs/%s-debug.html", 
					 getcwd(), date("Ymdhis"));
	static $fp;
	if(!$fp){
		$fp = fopen($fname, 'w');
		fwrite($fp, sprintf("<p>%s %s<br> via %s .</p>",
							$_SERVER['REQUEST_URI'], 
							$_SERVER['REQUEST_METHOD'], 
							$_SERVER['HTTP_REFERER']));
        fflush($fp);
	}
	fwrite($fp, $data);
    if($close){
        fflush($fp);
        fclose($fp);
    }
}

//////////////////////////////////////////
/////////////////////// COOP CLASS
class coopPage
{
	var $auth;
	var $debug = 0;					// debug level. used for printf's
	var $indexed_all; 			// legacy stuff. ALL of the callbacks
	var $userStruct;				// cache of legacy info ($u)
	var $bufferedOutput;			// to store buffered output, for QFC
	var $vars;                  //alias of session vars. i hope.
    var $title = 'Data Entry';  // the titlebar of the browser windowi
    var $heading = 'Pacifica Co-Op Nursery School Data Entry'; // to display
    var $currentSchoolYear;   // cache so i'm not pounding findschoolyear
    
	function coopPage($debug = false)
		{
			$this->debug = $debug;
			PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 
								   array(&$this, 'kensPEARErrorHandler'));
			dump("debug level $this->debug");
            $this->mergeSessionVars();
            $this->currentSchoolYear = findSchoolYear();
           
		}

	// for use with the old, non-object-oriented, homegrown auth/dispatcher
	function createLegacy($auth)
		{
			$this->auth = $auth;
			$this->userStruct =  getUser($auth['uid']);
		}


	// NOTE! prints directly to screen, doesn't return output!
	function header()
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
				   $doctype, $metalinks, $this->title, $this->heading);
			
			
			$this->debugCrap();
			warnDev();
			
			user_error("CoopPage.php: ------- NEW PAGE --------", 
					   E_USER_NOTICE);

		}


	function topNavigation()
		{
            $res = '';

			// i don't user this->page->userStruct
			// since it requires createlegacy adn i may not have that!
			$u = getUser($this->auth['uid']);	// ugh.

            /// TODO: yank HTML table and use CSS instead!
			$tab =& new HTML_Table('width="100%"');

			$tab->addCol(array(
							 sprintf("<h3>Welcome %s!</h3>", $u['username'])));
			$tab->addCol(array($this->selfURL(array(
								   'value' => "Back to Main Menu", 
                                   'inside' =>"action=menu", 
								   'base' => "index.php")))
						 ); // TODO: maybe make that backbutton hilighted?
			$tab->addCol(array($this->selfURL(array('value' =>"Log Out", 
													'inside' =>'action=logout'))));
			
			
						 
			$res .= $tab->toHTML();

			return $res;
		}
	



	function debugCrap()
	{
		$this->confessArray($_REQUEST, "test REQUEST");
		$this->confessArray($GLOBALS['HTTP_RAW_POST_DATA'], "RAW HTTP POST");
		$this->confessArray($_SESSION, 
                            "test SESSION (prior to request being processed)");
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
                $this->confessArray($this->userStruct, 'CoopPage->userStruct',
                                    4);
			} else{
				// show the login, then be done with this
				done();
			}

			$output = ob_get_clean();
			$output && ob_end_flush();

			return $output;


		}

    function forceUser($uid)
        {
            $this->auth['uid'] = $uid;
            $this->userStruct =  getUser($this->auth['uid']);
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

	function confessArray($array, $message, $level = 1, $outofband = true)
		{
			if($this->debug < $level){
				return;
			}
			// NOTE no need to print confessarray, $outofband tells it to
			if(is_array($array)){
                $res = confessArray($array, $message, $outofband);
            } else if(is_object($array)) {
                // XXX ALWAYS out of band, however!
                $res = $outofband ? '' : 'dumping object out of band. hack.';
                confessObj($array, $message);
            } else {
                $res = "<pre>====== $message ==========\n[$array]</pre>";
            }
			if($outofband){
				dump(&$res);
			} 
		}


	// XXX DOES THIS EVEN WORK??!
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


	// nifty way to get session vars outta there
	// fishes shit out of a request or session
	function requestOrSession($itemName){

        if(isset($_REQUEST[$itemName])){
            $_SESSION[$itemName] = $_REQUEST[$itemName];
        } else if(isset($_SESSION[$itemName])){
            $_REQUEST[$itemName] = $_SESSION[$itemName];
        }
        return $_REQUEST[$itemName];
	}
	


	// USAGE: selfURL(array(
	//					value="text to display",
	//					inside="var=value&morevar=morevalue",
	//							or an array of pairs
	//					base="page.php"
    //                  popup if you want it to be a javascript popup)
    //                  and par if you want paragraph separators (default) )
    //                  host = use SERVER[http host], for headerlocation
	// all of which are optional
	// if you do not specify inside, NO AUTH VARS ARE PASSED THROUGH! XXX
	// without any args, returns just coop session var for use in Header()
	function selfURL($args= false)
		{
            $value = isset($args['value']) ? $args['value'] : false;
            $inside = isset($args['inside']) ? $args['inside'] : false;
            $base = isset($args['base']) ? $args['base'] :false;
            $popup = isset($args['popup']) ? $args['popup'] :false;
            $par = isset($args['par']) ? $args['par'] :true;
            $host = isset($args['host']) ? $args['host'] :false;
            $title = isset($args['title']) ? $args['title'] :false;
            $elementid = isset($args['elementid']) ? $args['elementid'] :false;
            $tags = isset($args['tags']) ? $args['tags'] :false;


            $res = '';
            
			$base = $base ? $base : $_SERVER['PHP_SELF'];

            if(($pos = strpos($base, '?')) !== false) {
                $base = substr($base, 0, $pos);
            }


            if($host){
                // this is NOT redundant and i to NOT want to parse $base!
                // my toal here is to get the $PATH of selfurl.
                // fucking PHP can't
                // path_info(parse_url($_SERVER['PHP_SELF'])['path'])['dirname']
                $selfurl = parse_url($_SERVER['PHP_SELF']);
                $path = pathinfo($selfurl['path']);
                $dir = $path['dirname'];

                $this->confessArray($selfurl, 'php self', 5);
                $this->confessArray($path, 'pathinfo', 5);

                $base = sprintf('http://%s%s/%s', 
                                $_SERVER['HTTP_HOST'], $dir, $base);
            }
            


            if($value){
                $par && $res .= '<p>';
                if($tags){
                    $tagstr = "";
                    foreach($tags as $tag=>$tagval){
                        $tagstr .= sprintf('%s="%s" ', $tag, $tagval);
                    }
                }
                $res .= "<a $tagstr href=\"";
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
                
                if($title){
                    $res .= sprintf(' title = "%s" ', 
                                    htmlentities($title));
                }

                if($elementid){
                    $res .= sprintf(' id = "%s" ', $elementid);
                }

                $res .= sprintf('>%s</a>', $value);
                $par && $res .= '</p>';
            } 

            
            $this->confessArray($args, 
                                sprintf('CoopPage::selfURL() returning [%s]',
                                        htmlentities($res)), 
                                5);
            return $res; 
		}
    

	function formatURLVars($base, $inside = false)
		{
			$res = '';
            
			if($inside){
				if(is_array($inside)){
					foreach($inside as $var => $val){
                        if(is_array($val)){
                            // non fatal
                            $this->mailError("$var value is an array");
                        }
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


	function mailError($subject, $body = 'no body')
		{

			$body .= sprintf("\n\n--- BACKTRACE--\n%s", 
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
                $this->finalDebug();
                dump('PEAR ERROR DONE BEING DUMPED', true);
                // THIS EXITS SO MY QA SCRIPTS WILL CATCH IT!
                user_error('PEAR ERROR, saving to debug', E_USER_ERROR);
                exit(1);
			}
			$this->mailError('PEAR error on live site!',
							 print_r($obj, true));
            $this->done();
			exit(1);
		}


	// ugly hacks to deal with QFC
	function buffer($html, $additive = true)
		{
			if($additive){
				$this->bufferedOutput .= $html;
			} else {
				$this->bufferedOutput = $html;
			}
		}

	function flushBuffer($clean = true)
		{
			$res = $this->bufferedOutput;
			dump("flushing buffered output\n");
			if($clean && $res){
				ob_end_flush();
				$this->bufferedOutput = '';
			}
			return $res;
		}

	function printDebug($string, $level = 0, $outofband = true)
		{
			if($level > $this->debug){
				return;
			}

			if($outofband){
				$foo = "<p>DEBUG ";
				$bar = "</p>";
				dump($foo);
				dump($string);
				dump($bar);
				return;
			} 

			print "<p>DEBUG $string</p>";

		}

    function mergeSessionVars()
        {
            //OLD STUFF FIRST, THEN NEW STUFF. so REQ overrides SESSION!
            //TODO: empty() guards around this
            $_SESSION['cpVars'] =  array_merge($_SESSION['cpVars'], 
                                               $_REQUEST['cpVars']);
            $this->vars =& $_SESSION['cpVars'];
        }

    function finalDebug()
        {
            // things i always want to see at theend of a debug trace
            $this->confessArray(get_included_files(), 'file inclusion order', 
                                4);
            $this->confessArray($_SESSION, 
                                'CoopPage::done() saving SESSION  at END of page');
        }

    function done()
        {
            $this->finalDebug();
            print $this->flushBuffer();
            done(); // the legacy utils.inc version
            dump('all done' , true);
        }


    function stackPath()
        {
            $res = array();

            // cute, but useless
            // $path = array_map(create_function('$ar', 
            //'return($ar["table"]);'),
        //                    $this->vars['stack']);

            foreach($this->vars['stack'] as $stack){
                if(!count($stack) || empty($stack['table'])){
                    continue;
                }
                $co =& new CoopObject(&$this, $stack['table'], &$this);
//TODO: add a title (can i do that without a link?) with the concatlink
//                 if(is_numeric($stack['id']){
//                     $co->get($stack['id']);
//                 }
                $res[] = sprintf('%s %s',
                                 $co->actionnames[$stack['action']],
                                 $co->obj->fb_shortHeader ? 
                                 $co->obj->fb_shortHeader : 
                                 $co->table);

            }
            return count($res) ? implode(' &gt; ', $res) : '';
            
        }

    // gets the last stack item, IN PLACE t
    // this is so you can insert things into  it to be popped off later
    function &getPreviousStack()
        {
            if(empty($this->vars['stack'][count($this->vars['stack']) - 1])){
                return;
            }
            return $this->vars['stack'][count($this->vars['stack']) - 1];
        } 


    // destroys vars[last] and replaces it with last on stack
    // returns a copy of what was clobbered, or nothing if nothing popped
    function popOff()
        {
            // ALWAYS unset this here
            unset($this->vars['prev']); 

            /// only if there's something there
            /// XXX the isset(REQUEST[pop]) is wrong.
            /// last should EQUAL request at this stage!
            if(isset($this->vars['stack']) && count($this->vars['stack']) &&
                (isset($this->vars['last']['pop']) || isset($_REQUEST['pop'])))
            {
                $this->confessArray(
                    $this->vars, 
                    'popping off of the stack (replacing last with stack[n-1])', 
                    1);
                ///COPY not ref, the old one i'm about to clobber
                $prev = array_reverse(array_reverse($this->vars['last'])); 
                $this->vars['last'] = array_pop($this->vars['stack']);  
                $this->vars['prev'] = $prev;
                return $prev;
            }
            return; // return nothing if i didn't pop anything.
        }

    // cleans off teh whole stack
    function initStack()
        {
            $this->printDebug('initialising stack', 2);
            $this->vars['stack'] = array();
        }

    
    // the lastco is the coopobject(coopform) of the last['table']
    // i've already dedided what table to use well before calling mergerequest
    // XXX so move this to a coopobj or dispatcher, turn $lastco to $this!
    // last['table'] takes precedence over verything. it's magickal.
    function mergeRequest()
        {

            $this->confessArray($this->vars['last']['submitvars'], 
                                'CoopPage::mergeRequest() submitvars', 4);
            $this->confessArray($_REQUEST, 
                                'CoopPage::mergeRequest() REQUEST', 4);

            // note! submitvars OVERRIDES REQUEST!
            // necessary for pop's/push's
            $merged = array_merge_recursive($_REQUEST, 
                                         $this->vars['last']['submitvars']);


            // let the last id override! for edits.
            if($this->vars['last']['table']){
                $lastco =& new CoopObject(&$this, 
                                          $this->vars['last']['table'], 
                                         &$nothing);
                $last_long_pk = $lastco->prependTable($lastco->pk);
                
                if($this->vars['last']['id'] && !$merged[$last_long_pk])
                {
                    $merged[$last_long_pk] = $this->vars['last']['id'];
                }
            }
            $this->confessArray($merged, 
                                'CoopPage::mergeRequest() MERGED', 4);
            return $merged;
        }


    // NON LOCAL EXIT! basically.
    function headerLocation($url)
        {
            if(headers_sent($file, $line)){
                PEAR::raiseError("headers already sent! at $file line# $line", 666);
            }
            $this->printDebug("redirecting to $url", 1);
            $this->flushBuffer();
            header("Location: $url");
            // just in case!
            printf('<a href="%s">Click here</a> if you are not redirected.', 
                   $url);
            $this->done();
        }


    function yearNotSetupYet()
        {
            // TODO: just take them to year setup!
            PEAR::raiseError("You do not have this year set up correctly.",
                             666);

        }


    // returns the REAL last status: guesses based on prev. Hack hack hack!
    // if i popped, my result gets clobberd. so i have to use prev.
    function getStatus() 
        {
            if(!empty($this->vars['prev'])){
                $status =& $this->vars['prev'];
            } else {
                $status =& $this->vars['last'];
            }
            
            if(!empty($status['result'])){
                return $status['result'];
            }
        }

    // silly utility function for getting previous or some number back years
    function decrementSchoolYear($numyears = 1, $fromyear = false)
        {
            $year = $fromyear ? $fromyear : $this->currentSchoolYear;
            list($fall, $spring) = explode('-', $year);
            return sprintf('%d-%d', 
                           $fall - $numyears, 
                           $spring - $numyears);
        }


} // END COOP PAGE CLASS

////KEEP EVERTHANG BELOW

?>
