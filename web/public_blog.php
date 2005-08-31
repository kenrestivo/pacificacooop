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

//$debug = 4;


require_once("first.inc");
require_once("shared.inc");
require_once("CoopView.php");
require_once("HTML/Table.php");

PEAR::setErrorHandling(PEAR_ERROR_PRINT);


function getentries(&$cp)
{
    $co =& new CoopView(&$cp, 'blog_entry', &$none);
    print $co->obj->fb_display_summary();

}

////////////
///MAIN
$cp =& new CoopPage($debug);
$_SESSION['foo'] = 'foo';		// keep auth.inc happy
if($_REQUEST['summary']){
    getentries(&$cp);
} else {
    print "nothing here yet";
}



?>

<!-- PUBLIC BLOG -->
