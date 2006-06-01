<?php

//  Copyright (C) 2004-2006  ken restivo <ken@restivo.org>
// 
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
// 
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details. 
// 
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


//$Id$
// show the field order


require_once('../includes/first.inc');
require_once('COOP/Page.php');
require_once('COOP/Object.php');
//require_once('HTML/Table.php');


$cp = new coopPage( $debug);
//print $cp->pageTop();

print date('m/d/Y');


$page = new CoopPage(4);
$co =& new CoopObject(&$page, 'table_permissions', &$nothing);


$co->obj->query(
    'select distinct table_name from table_permissions order by table_name');

while($co->obj->fetch()){
    $tables[] = $co->obj->table_name;
}


print '<p>A nifty utility to show you all the tables for which anyone has permissions, and the ORDER of the fields as they will be displayed, which is of course in the order determined by CoopObject::reorder()</p>';

            
foreach($tables as $table){
    $err = ''; 
    $status = ''; 
    $targ =& new CoopObject(&$page, $table, &$co);

    $labels = array();
    printf('<h3>%s (%s)</h3>', $targ->obj->fb_formHeaderText, $table);

    foreach($targ->reorder($targ->obj->fb_fieldLabels) as $key => $title){
        $labels[] = sprintf('%s (%s)', 
                            $title,
                            $key);
    }

    printf('<ul><li>%s</li></ul>', implode('</li><li>', $labels));
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SHOWSTRUCT -->
 

