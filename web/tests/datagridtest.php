<?php 

//$Id$
require('Structures/DataGrid.php');
require('objects/Users.php');
DB_DataObject::debugLevel(5);

// Define the DataObject
$user = new Users();
$user->whereAdd("user_id > 0");

// Print the DataGrid
$dg = new Structures_DataGrid();
$dg->bind($user);
$dg->render();




////KEEP EVERTHANG BELOW

?>
<!-- END TEST -->


