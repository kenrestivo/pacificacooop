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
require_once("includes/auth.inc");

require_once("CoopPage.php");
require_once("CoopMenu.php");
require_once("CoopView.php");


//PEAR::setErrorHandling(PEAR_ERROR_PRINT); //  before page exists.



$cp =& new CoopPage($debug);

if(headers_sent($file, $line)){
    PEAR::raiseError("headers sent at $file $line ", 666);
}
print $cp->pageTop();


print $cp->topNavigation();
$cp->createLegacy($cp->auth);

$menu =& new CoopMenu(&$cp);
$menu->build();

$u = $cp->userStruct; // cached by createlegacy

print "\n<hr /></div> <!-- end header div -->\n";
//confessObj($menu, 'menu');


/// every hit to main menu should clear out the stack
///XXX every visit to home page wipes out the whole stack
///i suspect this is a bad idea, but it beats confusing people
$cp->initStack();



//confessObj($menu, "menuonb");
/// XXX AUUGH! for some reason this totaly breaks, only on the live site
/// if you don't have id=leftCol. makes no sense, there is no id, it's a class
print '<div class="leftCol" id="leftCol">';
print $menu->getDHTML();
print '<noscript>Alas, you must have a JavaScript-enabled browser in order to use this site. Sorry.</noscript>';
print '</div><!-- end leftcol div -->';

print '<div class="rightCol">';

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
                 $blog->obj->homepage_summary(&$blog),
                 $blog->actionButtons());


$cal =& new CoopView(&$cp, 'calendar_events', &$nothing);
print $cp->newMenuRow(&$tab, 
                 $cal->obj->fb_formHeaderText,
                 $cal->obj->homepage_summary(&$cal),
                 $cal->actionButtons());



print $tab->toHTML();


// extra condom. necessary, apparently.
// XXX MISERABLE hack, this.
$cp->initStack();

///////////////////////

// NOTE! i don't end rightcol div, since done does that for me
$cp->done();

/// DO NOT DELETE
?>
<!-- END INDEX -->
