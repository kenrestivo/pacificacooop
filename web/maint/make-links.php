<?php

//$Id$
// to move a few auction items over to in-kind items.

//chdir("../");                   // XXX only for "test" dir hack!

require_once("session-init.php");
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

// ok, now the actual links
//reset($tables);

foreach($tableFields as $table => $fields){
	foreach($fields as $field){
		if($pkeys[$field]){
			$links[$table][$field] = $pkeys[$field];
		}
	}
}

//reset($links);

//print_r($pkeys, "keys");
//print_r($tableFields, "tables");
//print_r($links, "links");


// ok format it for printing now
//reset($links);
foreach($links as $from => $tablelinks){
	$res .= sprintf("\n[%s]\n", $from);
	foreach($tablelinks as $id => $totable){
		$res .= sprintf("%s = %s:%s\n",
						$id, $totable, $id);
	}
}



// OBNOXIOUS! i have no idea why i need to do this
$res = preg_replace('/^Object/', '', $res);

print $res;

//printf("<pre>\n\n$res</pre>");

////KEEP EVERTHANG BELOW

?>


