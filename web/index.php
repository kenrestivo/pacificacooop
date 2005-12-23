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


//$debug = -1;

require_once("first.inc");
require_once("auth.inc");

require_once("CoopPage.php");
require_once("CoopMenu.php");
require_once("CoopView.php");


PEAR::setErrorHandling(PEAR_ERROR_PRINT); //  before page exists.

printf('%s

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head> %s
			<title>Data Entry</title>
		</head>

		<body>
<!--[if gte IE 5.5000]>
<script type="text/javascript" src="lib/pngfix.js"></script>
<![endif]-->

		<div id="header">
				<h2>Pacifica Co-Op Nursery School Data Entry</h2>',
	   $doctype, $metalinks);

warnDev();



$auth = logIn($_REQUEST);
if($auth['state'] != 'loggedin'){
	done();
}

//OK, i am logged in!
$cp =& new CoopPage($debug);
$cp->createLegacy($auth);

$menu =& new CoopMenu(&$cp);
$menu->createNew();

	
$u = $cp->userStruct; // cached by createlegacy
header('Assure-Top-Line-PHP-Hack: true');
print $cp->topNavigation();
print "\n<hr /></div> <!-- end header div -->\n";
//confessObj($menu, 'menu');



//confessObj($menu, "menuonb");
print '<div id="leftCol">';
print $menu->kenRender();
print '</div><!-- end leftcol div -->';

print '<div id="rightCol">';

$tab =& new HTML_Table();

//TODO: put this in as a generic function, in an object, using HTML_TABLE!
foreach($menu->alertme as $table){
    $alert =& new CoopView(&$cp, $table, &$nothing);
    $alertbody = $alert->getAlert();
    if($alertbody){
        $cp->newMenuRow(&$tab,
                   $alert->obj->fb_formHeaderText,
                   $alertbody,
                   $alert->actionButtons());
    }
}

// TODO: let the user configure what to show?
$blog =& new CoopView(&$cp, 'blog_entry', &$nothing);
print $cp->newMenuRow(&$tab, 
                 $blog->obj->fb_formHeaderText,
                 $blog->obj->fb_display_summary(&$blog),
                 $blog->actionButtons());


$cal =& new CoopView(&$cp, 'calendar_events', &$nothing);
print $cp->newMenuRow(&$tab, 
                 $cal->obj->fb_formHeaderText,
                 $cal->obj->fb_display_summary(&$cal),
                 $cal->actionButtons());



print $tab->toHTML();

///XXX every visit to home page wipes out the whole stack
///i suspect this is a bad idea, but it beats confusing people
$cp->initStack();

///////////////////////

// NOTE! i don't end rightcol div, since done does that for me
$cp->done();

/// DO NOT DELETE
?>
<!-- END INDEX -->
