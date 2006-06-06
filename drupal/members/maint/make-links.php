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



// $Id$
// vital script to automatically generate the foreign keys based on the id
// this is because MySQL has no concept of foreign keys


require_once("includes/session-init.php");
require_once('DB.php');

setupDB();

// I LOVE SCHEME. this was ported straight from a guile script.

global $dburl;
$db =& DB::connect($dburl);
if (DB::isError($db)) {
    die($db->getMessage());
}

// get tables
$res =& $db->query("show tables");
while($row =& $res->fetchRow()){
	$tables[] = $row[0];
}

// get keys
foreach($tables as $table){
	$res =& $db->query("explain $table");
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		//confessArray($row, "row $table");
		if($row['Key'] == 'PRI'){
			$pkeys[$row['Field']] = $table;
		} else {
			$tableFields[$table][] = $row['Field'];
		}
	}

}

// stick the manuals in there first
$links = parse_ini_file('schema/coop-manual-links.ini', TRUE);
//print_r($links);


// ok, now the actual links
//reset($tables);

foreach($tableFields as $table => $fields){
	foreach($fields as $field){
		if($pkeys[$field]){
			$links[$table][$field] = $pkeys[$field];
		}
	}
}



//print_r($pkeys);
//print_r($tableFields);
//reset($links);
//print_r($links);

// ok format it for printing now
//reset($links);
foreach($links as $from => $tablelinks){
	$res .= sprintf("\n[%s]\n", $from);
	foreach($tablelinks as $id => $totable){
        if(strstr($totable, ':')){
            $res .= sprintf("%s = %s\n", $id, $totable);
        } else {
            $res .= sprintf("%s = %s:%s\n",
                            $id, $totable, $id);
        }
	}
}



// OBNOXIOUS! i have no idea why i need to do this
$res = preg_replace('/^Object/', '', $res);

print $res;

//printf("<pre>\n\n$res</pre>");

////KEEP EVERTHANG BELOW

?>


