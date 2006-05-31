<?php

//$Id$
// to move a few auction items over to in-kind items.


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


