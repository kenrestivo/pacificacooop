<?php

//$Id$
// to move a few auction items over to in-kind items.

chdir("../");                   // XXX only for "test" dir hack!

require_once("session-init.php");
require_once('DB.php');

setupDB();



// aaptes from make-links. some duprication of code

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

// get whitey
foreach($tables as $table){
	$res =& $db->query("explain $table");
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		//confessArray($row, "row $table");
		if($row['Type'] == 'longtext'){
            printf('alter table %s add column %s_cache varchar(255);<br />',
                   $table, $row['Field']);
            // OK populate it now
            
		}
	}

}

////KEEP EVERTHANG BELOW

?>


