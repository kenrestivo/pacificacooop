<?php
//<!-- $Id$ -->
#	silly hack for printing

#  Copyright (C) 2004  ken restivo <ken@restivo.org>
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


require_once("first.inc");
require_once("auth.inc");
require_once("shared.inc");
require_once("CoopPage.php");
require_once("CoopView.php");
require_once("thank_you_notes.inc");

//global hacks to save ken's wrists
$callbacks = $thank_you_callbacks;
$fields = $thank_you_fields;


PEAR::setErrorHandling(PEAR_ERROR_PRINT);

//override
 $metalinks = '<meta http-equiv="content-type" 
 				content="text/html; charset=iso-8859-1">
				<link rel="stylesheet" href="print.css" title="main" >';


//header stuff
printf('<HTML lang="en"> <HEAD> %s

<TITLE>%s</TITLE> 
</HEAD> <BODY> <div id="page">',
	   $metalinks, "Thank You Note"); // TODO put name of solicit contact?


user_error("states.inc: ------- NEW PAGE --------", E_USER_NOTICE);


//confessArray($_REQUEST, $_SERVER['PHP_SELF']);

$auth = logIn($_REQUEST);


if($auth['state'] != 'loggedin'){
	done();
}

$cp =& new CoopPage();
$cp->createLegacy($auth);


//will need these to verify actions
$realm = $callbacks['realm'];
$p = getAuthLevel($auth, $realm);
$u = $cp->userStruct; // createlegacy keeps this for me



//now for the ui stuff!

#i am only dealing with ONE entry in this form: entry0
$fs = inputToFieldStruct($_REQUEST['entry0'], $fields);
//confessArray($fs, "fs");


$ty =& new ThankYou(&$cp);
$ty->repairOrphaned();


//confessArray($_REQUEST, 'REQ');

switch($_REQUEST['subaction']){
 case 'reprint':
	$tid = $fs['thank_you_id']['def'];
	$ty =& new ThankYou(&$cp);
	 $ty->recoverExisting($tid);
	 $ty->substitute();
	 print $ty->toHTML();
	 break;
 case 'print':
	 $fs['family_id']['def'] = $u['family_id']; // hack, but it works
	 $ty =& new ThankYou(&$cp);
	 $ty->check_reconcile = $_REQUEST['reconcile'];
	 if(!$ty->findThanksNeeded($_REQUEST['pk'], $_REQUEST['id'])){
		 print "<p>This thank-you has already been entered. 
			Close this window and check 'View/Edit' in the other window.</p>";
		 break;
	 }
	 $ty->findThanksNeeded($_REQUEST['pk'], $_REQUEST['id'], 'Letter');
	 $ty->substitute();
	 print $ty->toHTML();
	 break;
}


print "</body></html>";

?>
<!-- END PRINTPOPUP -->
