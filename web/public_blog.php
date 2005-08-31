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
require_once("CoopObject.php");
require_once("HTML/Table.php");

PEAR::setErrorHandling(PEAR_ERROR_PRINT);



function getentries(&$cp)
{
    $co =& CoopObject(&$cp, 'blog_entry', &$none);
    $co->obj->query("select blog_entry.*, audit_trail.updated from blog_entry 
left join audit_trail on audit_trail.index_id = blog_entry.blog_entry_id 
where table_name = 'blog_entry' 
order by updated desc
limit 4");
    $co->obj->find(true);

    while($co->obj->fetch()){
        printf("<p><b>%s</b><p>%s (Posted %s)</p>", 
               );
    }

}

////////////
///MAIN
$cp =& new CoopPage();
$_SESSION['foo'] = 'foo';		// keep auth.inc happy
if($_REQUEST['summary']){
    getentries();
} else {
    print "nothing here yet";
}



?>

<!-- PUBLIC BLOG -->
