<?php

/*******************
	utilities used by auth and shared, which contain no object-specific stuff

  Copyright (C) 2003-2005  ken restivo <ken@restivo.org>
<!-- $Id$ --> 
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
**********************/


require_once("HTML/Table.php");


###############
# TDARRAY
# inputs: an array
# outputs: a table row (td's)
# saves ken's wrists from carpool tunnel syndrome. 
# i hate html
##############
function tdArray($arr, $align='align="left"', $font = "") {
	printf('<tr valign="top" %s>',$align);
    while ( list( $key, $val ) = each($arr)) {
		printf("\t<td>%s</td>\n", $val);
    }
	print "</tr>\n";
}###END TDARRAY

###############
# THARRAY
# inputs: an array
# outputs: a table row (th's)
# saves ken's wrists from carpool tunnel syndrome. 
# i hate html
##############
function thArray($arr, $align='align="left"') {
	printf('<tr valign="top" bgcolor="#aabbff" %s>', 
		   $align);
    while ( list( $key, $val ) = each($arr)) {
		print "<th>$val</th>";
    }
	print "</tr>\n";
}###END TDARRAY


###############
# TRARRAY
# inputs: an array
# outputs: a single table column (tr's)
# saves ken's wrists from carpool tunnel syndrome. 
# i hate html
##############
function trArray($arr) {
    while ( list( $key, $val ) = each($arr)) {
		print "\n<tr>\n$val</tr>\n";
    }

}###END TDARRAY



###############
# CONFESSVARS
# outputs: the global array of vars passed in through POST/GET
# vital sanity-saver when debugging forms that don't work
###############
function confessVars()
{
	global $HTTP_POST_VARS;
	global $HTTP_GET_VARS;

	if ($HTTP_POST_VARS) {
        $httpvars = $HTTP_POST_VARS;
        echo "<br> you POSTED these\n";
    } elseif ($HTTP_GET_VARS){
        $httpvars = $HTTP_GET_VARS;
        echo "<br> you GETTED these\n";
    } else {
        echo "<br>hey, you didn't send me any vars!!<br>\n";
    }
	if($httpvars){
		print_r($httpvars);
		print "\n\n<table border=1>
		\n<tr>\n\t<td><b>var</td>\n\t<td><b>value</td>\n</tr>\n ";

		while (list($var, $value) = each($httpvars)){
			echo "\n<tr>\n\t<td>$var</td>\t<td>$value</td>\n</tr>\n";
		}
		reset($httpvars);
		print "\n</table>\n\n";
	}
	print "<br>";

	//XXX umm, isn't this redundant?
	confessArray($httpvars, "raw POST/GET vars");

} #END CONFESSVARS

###############
# CONFESSARRAY
# outputs: the details of the array passed in through POST/GET
# vital sanity-saver when debugging forms that don't work
###############
function confessArray($fields, $message, $return = false)
{
	$res .= "<pre>";
	$res .= "DEBUG $message\n";
//$res .=_r( debug_backtrace());
	if(is_array($fields)){
		$res .= htmlentities(print_r($fields, true));
		reset($fields);
	} else {
		$res .= "um... <$fields> is not an array\n";
	}
	$res .= "</pre>";

$return || print $res;

return $res;
} #END CONFESSARRAY

function confessBacktrace()
{
	confessArray(debug_backtrace(), 'backtrace');
}


######################
# AREYOUSURE
######################
function areYouSure()
{

} #END AREYOUSURE


##################
#	DONE
##################
function done()
{
	global $coop_sendto;

	print "</div><!-- rightcol div end -->";	
	print '<div id="footer"><hr>';
    print '<p>
      <a href="http://validator.w3.org/check/referer"><img
          src="http://www.w3.org/Icons/valid-xhtml10" name="validator"
          alt="Valid XHTML 1.0!" height="31" width="88" border="0" /></a>
    ';
    ///note teh tag closing. it's an html validator bug waiting to happen ;-)
	printf("If anything on this site appears to be not working properly, 
			email <a href='mailto:%s'>%s</a> or call %s at %s</p>",
			$coop_sendto['email_address'], $coop_sendto['email_address'], 
			$coop_sendto['name'], $coop_sendto['phone'] );
	/* AUUGH! testcasetool is case sensitive, and for some reason apache
		lowers the case of my tags only when checkloing was used. weird.
	*/
	print "</div><!-- end footer div -->"; // closes out bottom div
	
	print"</body></html> ";
    
	ob_end_flush();
	session_write_close();		// redundant?
	exit;
} #END DONE


/******************
	WARNDEV
	warn people if this is a dev site
******************/
function
warnDev()
{
	if(preg_match("/dev/", $_SERVER['PHP_SELF'])){
		print '<div id="warning"><p><strong>NOTE! THIS IS THE DEV SITE! 
				THIS IS NOT LIVE DATA, THIS IS ONLY FOR TESTING.</strong>
				<br>If you want the live site, use the "members" link, 
				NOT "members-dev". Thanks.</p></div>';
	}
	
}/* END WARNDEV */

/*******************
	JAVASUBMIT
	does that wacky bit of javascript to auto-submit a form
	outputs: javascript for auto-sumbitting a form
******************/
function
javaSubmit()
{
	// wrapped in comments to make old browsems ignore it
	print '<script type="text/javascript" language="javascript">
			<!-- Begin 
			function submitForm() {
				document.forms[0].submit(); 
				return(true); 
			} 
			// End -->
			</script>';
} /* END JAVASUBMIT */

/*******************
	TIMESTAMPDBPHP
	takes a mysql timestamped datetime, and makes it human-readable
	inputs: an sql-formatted date
	ouutputs: a human-formatted date
*******************/
function 
timestamp_db_php($date)
{
	//2004-12-04 19:07:14
	if(preg_match('/(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})/', 
				  $date, $matches)){
		$year   = $matches[1];
		$month  = $matches[2];
		$day    = $matches[3];
		$hour   = $matches[4];
		$minute = $matches[5];
		$second = $matches[6];

	} else {
		$year   = substr($date,0,4);
		$month  = substr($date,4,2);
		$day    = substr($date,6,2);
		$hour   = substr($date,8,2);
		$minute = substr($date,10,2);
		$second = substr($date,12,2);
	}

	$ampm = $hour < 12 ? "AM" : "PM";
	if($hour > 12){
		$hour -= 12;
	}
	/*$epoch = date("U", mktime($time[0],$time[1],$time[2],
						$datebreak[1],$datebreak[2],$datebreak[0])); */
	$datetime = sprintf("%02d/%02d/%04d",
						$month, $day, $year);
    
    // don't show time if it is midnight, or nonexistent
    if($hour + $minute + $second){
        $datetime .= sprintf(" %02d:%02d%s",
                             $hour,$minute, $ampm );
    }
	return $datetime;
} /* EDN TIMESTAMPEDBPHP */



/*******************
	HUMANTOSQLDATE
	takes a human-formatted date, and make it palatable to sql
	inputs: a human-formatted date
	outputs: an sql-formatted date
*******************/
function
human_to_sql_date($dt)
{
	if($dt && preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $dt, $cl) >0) {
		user_error("human_to_sql_date($dt) is already a legal sql date",
				   E_USER_NOTICE);
		return $dt;
	}
	if($dt && preg_match("/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/", 
						 $dt, $cleaned) < 1){
		user_error("human_to_sql_date($dt) was passed an invalid value!!",
				   E_USER_ERROR);
	}
	//confessArray($cleaned, "human_to_sql_date($dt)");
	$cleandate = sprintf("%04d-%02d-%02d",
						 $cleaned[3], $cleaned[1], $cleaned[2]);
	user_error("human_to_sql_date(): changed [$dt] to [$cleandate]",
			   E_USER_NOTICE);
	return $cleandate;
} /* END HUMANSQLTODATE */

/*******************
	HUMANTOSQLTIME
        this function ought to be taken out and shot
	takes a human-formatted date, and make it palatable to sql
	inputs: a human-formatted date
	outputs: an sql-formatted date
*******************/
function
human_to_sql_timestamp($dt)
{
    // IS ALREADY LEGAL SQL (then skip)
	if($dt && preg_match("/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}:d{2}(:\d{2})*)$/", 
                         $dt, $cl) >0) {
		user_error("human_to_sql_time($dt) is already a legal sql date",
				   E_USER_NOTICE);
		return $dt;
	}
    // IS LEGAL HUMAN?
	if($dt && preg_match(
           "/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s*?((\d{1,2}):(\d{2})(:\d{1,2})*)*\s*(\w{2})*$/", 
						 $dt, $cleaned) < 1 )
    {
        $err = "human_to_sql_time($dt) was passed an invalid value!!";
		PEAR::raiseError($err, E_USER_ERROR);
        //user_error($err, E_USER_NOTICE);
	}
	//confessArray($cleaned, "human_to_sql_timestamp($dt)");
	$cleandate = sprintf("%04d-%02d-%02d",
						 $cleaned[3], $cleaned[1], $cleaned[2]);
    if($cleaned[5]){
        $cleandate .= sprintf(' %02d:%02d',
                              $cleaned[8]? $cleaned[5]+12: $cleaned[5], 
                              $cleaned[6]);
    }
	user_error("human_to_sql_time(): changed [$dt] to [$cleandate]",
			   E_USER_NOTICE);
	return $cleandate;
} /* END HUMANSQLTODATE */



/*******************
	SQLTOHUMANDATE
	takes an sql-formatted date, and make it readable by mere mortals
	inputs: a human-formatted date
	outputs: an sql-formatted date
*******************/
function
sql_to_human_date($dt)
{
    // NOTE: this pregmatch *MUST* match the one in human_to_sql_timestamp
	if(preg_match("/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s*?((\d{1,2}):(\d{2})(:\d{1,2})*)*\s*(\w{2})*$/",
				  $dt, $cleaned) > 0){
		user_error("sql_to_human_date($dt) is already in human format",
				   E_USER_NOTICE);
		return $dt;
	}
	if($dt && preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $dt, $cl) < 1){
		user_error("sql_to_human_date($dt) is not a legal sql date!!",
				   E_USER_NOTICE);
	}

	//capture sql 0000 date, which should be null
	if(($cl[1] + $cl[2] + $cl[3]) < 1){
		return "";
	}
	$cleandate = sprintf("%02d/%02d/%04d",
						 $cl[2], $cl[3], $cl[1]);
	user_error("sql_to_human_date(): changed [$dt] to [$cleandate]",
			   E_USER_NOTICE);
	return $cleandate;
} /* END SQLDAAETOHUMANDATE */




/*******************
	UNHTML
	sample code from php doc.
	inputs: html-laden string
	outputs: missing all the html
********************/
function
unHTML($string)
{
	$trans_tbl = get_html_translation_table (HTML_ENTITIES);
    $trans_tbl = array_flip ($trans_tbl);       
	return strtr ($string, $trans_tbl);  
}
// END UNHTML


/*********************
	KENARRAYFILL
	because having old versions of php sucks
**********************/
function
ken_array_fill($start, $count, $val)
{
	$make = array();
	for($i = $start; $i < $start + $count; $i++){
		$make[$i] = $val;
	}
	return $make;
} /* END KENARRAYFILL */

/*********************
	SHOWRAWQUERY
	simple hack for showing status reports
    inputs: title string to display, the query string, 
            whether to show dollar signs, and whether to show grand total
		returns: the html
**********************/
function
showRawQuery($title, $q, $dollaz = 0, $dogt = 1)
{


	$listq = mysql_query($q);
	
	$toptab = new HTML_Table(' bgcolor="#aa99ff" cellpadding="0" cellspacing="0"');

	$tab = new HTML_Table(' bgcolor="#ffffff"');	

	$i = 0;
	$err = mysql_error();
	if($err){
		user_error("showrawquery($title): [$q]: $err", E_USER_ERROR);
	}
	while($row = mysql_fetch_assoc($listq)){
		$tdrow = array();
		if($i < 1){
			while ( list( $key, $val ) = each($row)) {
				$headers[] = $key;
				$grand[] = 0;
			}
			$tab->addRow($headers, ' bgcolor="#aabbff"', "TH");
			reset($row);
			$i++;
		}
		$i = 0;
		while ( list( $key, $val ) = each($row)) {
			if($dollaz && $i){
				$tdrow[] = $val > 0 ? 
					sprintf("$%s",number_format($val,2,'.',","))
					: '';
			} else {
				$tdrow[] = $val;
			}
			$grand[$i++] += $val;
		}
		$tab->addRow($tdrow, 'align=right');
	
	}
	$s = count($headers);
	//confessArray($headers, "headers($s)");
	user_error("showRawQuery([$q]): count $s", E_USER_NOTICE);
	if($dogt){
		$grand[0] = 'GRAND TOTAL'; // always 0!
		// go thru and format them if they are numbers
		foreach($grand as $val){
			if(is_numeric($val)){
				$grandfixed[] = sprintf("%s%s",
										$dollaz ? "$" :'',
										number_format($val,
													  $dollaz ? 2 : 0,
													  '.',","));
			} else {
				$grandfixed[] = $val;
			}
		}
		//confessArray($grand, "grand($s)");
		$tab->addRow($grandfixed,'bgcolor="#aa99ff" align=right' );
	}

	$tab->altRowAttributes(1, 'bgcolor="#dddddd"', 
								   'bgcolor="#ccccff"');

	$tab->setColAttributes(0, 'align="left"');

	$toptab->addRow(array($title), 'align="center"', "TH");
	$toptab->addRow(array($tab->toHTML()));

	return $toptab->toHTML();

} /* END SHOWRAWQUERY */


/******************
	MISCQUERY
	a utility function for random non-select queries,
	i.e. inserting random things, or dicking wiht temp tables
	inputs: the query
	returns: affected rows, if nay
******************/
function
miscQuery($query)
{
	//ok, let's do it
	user_error("miscQuery)() doing [$query]", E_USER_NOTICE);
	if(mysql_query($query)){
		$rows = mysql_affected_rows();
	}
	$err = mysql_error();
	if($err){
		user_error("miscQuery(): [$query]: $err", E_USER_ERROR);
	}
	return $rows;
}/* END MISCQUERY */

/**********************
	FINDSCHOOLYEAR
	returns the school year for a particular date
	inputs: a date, in yyyy-mm-dd format, or NULL to use today's date
		guess to guess date if nothing in db, 
		and increment to use NEXT year not this one
	returns: the school year in yyyy-yyyy format
***********************/
function
findSchoolYear($date = 0, $guess = 1, $increment = 0)
{

	if(!$date){
		$date = date("Y-m-d");
		user_error("findSchoolYear(): no date supplied, using [$date]",
				   E_USER_NOTICE);
	}

	// grab mdy
	$tmp = preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $mdy) ;
	if($tmp < 1){
		user_error("findSchoolYear(): [$date] is not yyyy-mm-dd",
				   E_USER_ERROR);
	}

	if($increment){
		//force next year, and sync it up for query purposes too
		$mdy[1]++;
		$date = sprintf("%4d-%2d-%2d", $mdy[1], $mdy[2], $mdy[3]);
	}


	//TODO: look up the year in the db

	//ok, nothing there, so guess
	if($guess){
		user_error("findSchoolYear(): nothing in db for [$date], guessing...", 
				   E_USER_NOTICE);
	} else {
		//TODO: throw up an error on screen, with a link to the schoolyear setup
		user_error("findSchoolYear(): nothing in db for [$date], and you don't want me to guess", E_USER_ERROR);
	}

	$guessstart = 9; //assume sep1 is beginning of school year
	if($mdy[2] < $guessstart){
		return sprintf("%4d-%4d", $mdy[1] - 1, $mdy[1] );
	} else {
		return sprintf("%4d-%4d", $mdy[1], $mdy[1] + 1 );
	}
	
} /* END FINDSCHOOLYEAR */


// returns 1 if i am on the dev site, otherwise 0
function devSite()
{
	$parth = pathinfo($_SERVER['SCRIPT_FILENAME']);
	$dir = $parth['dirname'] ;
	// IF YOU EVER MOVE TO ANOTHER HOSTER, DEAL WITH THIS
	if(preg_match('/-dev/', $dir) > 0 || preg_match('/pacsql/', $dbhost) < 1) 
	{
		return 1;
	}
	return 0;
}

// EEEEEEEVIL! but,needed.
function javaPopup()
{
	return '<SCRIPT LANGUAGE="JavaScript">
<!-- Begin
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, \'" + id + "\', \'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=0,width=480,height=640,left = 272,top = 64\');");
window.location.reload( false );
}
// End -->
</script>';

}

/// XXX DUPLICATION OF CODE WITH lib/customdatebox.php::_getJs()
function
dateJava()
{
	//i use this in quickform too, so i'm playing nice here.
	if (!defined('HTML_QUICKFORM_CUSTOMDATEBOX_EXISTS')) {
		define('HTML_QUICKFORM_CUSTOMDATEBOX_EXISTS', true);
		// wrapped in comments to make old browsems ignore it
               $js .= '
function todaysDate(datefield) {
		var mydate=new Date()
		var theyear=mydate.getYear()
		if (theyear < 1000)
				theyear+=1900
		var theday=mydate.getDay()
		var themonth=mydate.getMonth()+1
		if (themonth<10)
				themonth="0"+themonth
		var theday=mydate.getDate()
		if (theday<10)
				theday="0"+theday

		var displayfirst=themonth
		var displaysecond=theday
		var displaythird=theyear

		datefield.value=displayfirst+"/"+displaysecond+"/"+displaythird
}
               ';
               $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
				   $js . "//]]>\n</script>";
           }
           return $js;

} // END DATEJAVA
 

 

//EOF!!!  DON'T DELETE PAST HERE, I NEED THAT STUFF
?>