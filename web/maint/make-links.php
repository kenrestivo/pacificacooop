<?php

//$Id$
// to move a few auction items over to in-kind items.

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('DB.php');


$debug = 0;

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();


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
reset($tables);
foreach($tables as $table){
	foreach($tableFields[$table] as $field){
		if($pkeys[$field]){
			$links[$table][$field] = $pkeys[$field];
		}
	}
}


//confessArray($pkeys, "keys");
//confessArray($tableFields, "tables");
//confessArray($links, "links");



// ok format it for printing now
reset($links);
foreach($links as $table => $tablelinks){
	$res .= sprintf("\n[%s]\n", $table);
	foreach($tablelinks as $id => $totable){
		$res .= sprintf("%s = %s:%s\n",
						$id, $totable, $id);
	}
}

//ken's hacky one that doesn't fit the mold
$res .= "\n[audit_trail]\naudit_user_id = users:user_id\n";

printf("<pre>\n\n$res</pre>");

done ();

////KEEP EVERTHANG BELOW

?>


<!-- END IMPORTRASTA -->


