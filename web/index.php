<?php
//<!-- $Id$ -->

#  Copyright (C) 2003,2004  ken restivo <ken@restivo.org>
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
require_once("shared.inc");
require_once("auth.inc");

require_once("roster.inc");
require_once("members.inc");
require_once("everything.inc");
require_once("CoopPage.php");
require_once("CoopMenu.php");

PEAR::setErrorHandling(PEAR_ERROR_PRINT);

print '<HTML>
		<HEAD>
				<link rel=stylesheet href="main.css" title=main>
			<TITLE>Data Entry</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School Data Entry</h2>
	';

warnDev();



$auth = logIn($_REQUEST);
if($auth['state'] != 'loggedin'){
	done();
}

//OK, i am logged in!
$cp =& new CoopPage;
$menu =& new CoopMenu;
$cp->auth = $auth;
$menu->createLegacy(&$cp);

	
$u = getUser($auth['uid']);

print $menu->topNavigation();
print "\n<hr>\n";
//confessObj($menu, 'menu');



//confessObj($menu, "menuonb");
print '<div id="leftcol">';
print $menu->kenRender();
print '</div>';

print '<div id="rightCol">';
print "<p>Please choose an action:</p>";

print "\n\n<table border=0>\n";
//	tdArray( array ("Description", "Summary", "Actions"), 'align=center');

	//narsty-ass ugly hack

$everything = array_merge($members_everything,  $sf_everything);
	
while ( list( $key, $val ) = each($everything)) {
	user_error(sprintf("main(): showing row for %s", $val['description']),
			   E_USER_NOTICE);
	// hack around the $callbacks not yet including fields, which it SHOULD
	showMenuRow($auth, $u, $val, ${$val['fields']});
}
	

/* admin 
	XXX can't use standard showMenuRow? 
		it uses FAMILYID, but admin wants USERID
	showMenuRow($auth, $u, 'User Administration', 
		'adminSummary', 'user', 'admin.php');
*/

print "\n</table>\n\n";

familyDetail($u['family_id']);
print "</div>";

///////////////////////


done();

/// DO NOT DELETE
?>
<!-- END INDEX -->
