<!-- $Id$ -->
<?php

/*******************
	utilities used by auth and shared, which contain no object-specific stuff

  Copyright (C) 2003,2004  ken restivo <ken@restivo.org>
 
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


###############
# TDARRAY
# inputs: an array
# outputs: a table row (td's)
# saves ken's wrists from carpool tunnel syndrome. 
# i hate html
##############
function tdArray($arr, $align="align='left'", $font = "") {
	print "\n<tr valign='top' $align>\n";
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
function thArray($arr, $align="align='left'") {
	print "\n<tr valign='top' $align>\n";
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
function confessArray($fields, $message)
{
	print "<plaintext>";
	print "DEBUG $message\n";
	if(is_array($fields)){
		print_r($fields);
		reset($fields);
	} else {
		print "um... <$fields> is not an array\n";
	}
	print "</plaintext>";
} #END CONFESSARRAY



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

	print "\n<hr>\n";
	printf("<br><small>If anything on this site appears to be not working properly, 
			email <a href='mailto:%s'>%s</a> or call %s at %s</small>",
			$coop_sendto['email_address'], $coop_sendto['email_address'], 
			$coop_sendto['name'], $coop_sendto['phone'] );
	/* AUUGH! testcasetool is case sensitive, and for some reason apache
		lowers the case of my tags only when checkloing was used. weird.
	*/
	print"</body> </html> ";
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
		print "<p><font color='#ff0000'>NOTE! THIS IS THE DEV SITE! 
				THIS IS NOT LIVE DATA, THIS IS ONLY FOR TESTING.</font>
				<br>If you want the live site, use the 'sf' link, 
				NOT 'sf-dev'. Thanks.";
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
	print "<script language=javascript>
			function submitForm() {
				document.forms[0].submit(); 
				return(true); 
			} 
			</script>";
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
	$year   = substr($date,0,4);
	$month  = substr($date,4,2);
	$day    = substr($date,6,2);
	$hour   = substr($date,8,2);
	$minute = substr($date,10,2);
	$second = substr($date,12,2);
	/*$epoch = date("U", mktime($time[0],$time[1],$time[2],
						$datebreak[1],$datebreak[2],$datebreak[0])); */
	$datetime = sprintf("%02d/%02d/%04d&nbsp;%02d:%02d:%02d",
						$month, $day, $year,$hour,$minute, $second );
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
	SQLTOHUMANDATE
	takes an sql-formatted date, and make it readable by mere mortals
	inputs: a human-formatted date
	outputs: an sql-formatted date
*******************/
function
sql_to_human_date($dt)
{
	if(preg_match("/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/", 
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
**********************/
function
showRawQuery($title, $q, $dollaz = 0, $dogt = 1)
{
	print "<h3>$title</h3>";
	$listq = mysql_query($q);
	print "<table border='0'>";
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
			thArray($headers);
			reset($row);
			$i++;
		}
		$i = 0;
		while ( list( $key, $val ) = each($row)) {
			if($dollaz && $i){
				$tdrow[] = sprintf("$%0.2f",$val);
			} else {
				$tdrow[] = $val;
			}
			$grand[$i++] += $val;
		}
		tdArray($tdrow, "");
	}
	$s = count($headers);
	//confessArray($headers, "headers($s)");
	user_error("showRawQuery([$q]): count $s", E_USER_NOTICE);
	if($dogt){
		$grand[0] = 'GRAND TOTAL'; // always 0!
		//confessArray($grand, "grand($s)");
		tdArray($grand);
	}
	print "</table>";
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




//EOF!!!  DON'T DELETE PAST HERE, I NEED THAT STUFF
?>
<!-- END UTILS -->



