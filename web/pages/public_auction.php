<?php 

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
require_once("shared.inc");
require_once("CoopView.php");

PEAR::setErrorHandling(PEAR_ERROR_PRINT);



///MAIN
$sy = findSchoolYear();
$tmp = explode('-', $sy);
$sfyear = $tmp[1];

$cp =& new CoopPage($debug);
$_SESSION['foo'] = 'foo';		// keep auth.inc happy




$cp->title = "Springfest $sfyear"; 
print $cp->header();
printf('<img src="%s/custom_font.php?text=Join%%20us%%20for%%20Springfest%%20%s&amp;size=18" alt="Join us for Springfest %s"/>', 
       COOP_ABSOLUTE_URL_PATH, $sfyear, $sfyear);
print "\n</div> <!-- end header div -->\n";

print '<div class="leftCol" id="leftCol">';

$sp =& new CoopView(&$cp, 'sponsorships', &$none);
print $sp->obj->public_sponsors(&$sp, $sy);

$ad =& new CoopObject(&$cp, 'ads', &$none);
print $ad->obj->public_ads(&$cp, $sy);


$inkind =& new CoopObject(&$cp, 'in_kind_donations', &$none);
print $inkind->obj->public_donors(&$inkind, $sy);
print '</div><!-- end leftcol div -->';


///// the main stuff
print '<div class="rightCol">';

// show year-specific HTML
$prettyname = sprintf("static/%s-springfest.template.html", 
					  $sfyear);
if(file_exists($prettyname)){
	print '<div id="springfestpretty">';
	include($prettyname);
	print "</div><!-- end springfestpretty div -->";
}


$pac =& new CoopObject(&$cp, 'packages', &$none);
print $pac->obj->public_packages(&$cp, $sy);


print "<p><a href='../'>Home</a></p>
";

print "</div><!-- end rightcol div -->";

print "</body></html>";
?>

<!-- PUBLIC AUCTION -->
