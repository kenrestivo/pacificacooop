<?php 

//$Id$
require_once('Structures/DataGrid.php');
require_once('DB/DataObject.php');

DB_DataObject::debugLevel(5);
chdir("../");                   // XXX only for "test" dir hack!
require_once("object-config.php");



// Define the DataObject

$user =& DB_DataObject::factory ('users'); // & instead?
if (PEAR::isError($obj)){
    die ($obj->getMessage ());
}

$user->find();

// Print the DataGrid
$dg = new Structures_DataGrid();
while ($user->fetch()){
	$ar = $user->toArray();
    $rec = new Structures_DataGrid_Record($ar);
    $dg->addRecord($rec);
}
$dg->render();




////KEEP EVERTHANG BELOW

?>
<!-- END DATAGRIDTEST -->


