<?php 


//$Id$
require_once('Structures/DataGrid.php');
require_once('DB/DataObject.php');


DB_DataObject::debugLevel(5);
chdir("../");                   // XXX only for "test" dir hack!
require_once("object-config.php");

// test hack
print '<HTML>
				<HEAD>
						<link rel=stylesheet href="../main.css" title=main>
							<TITLE>Data Entry</TITLE>
				</HEAD>
				<BODY>
				<h2>Pacifica Co-Op Nursery School Data Entry</h2>
				';



// Define the DataObject

$user =& DB_DataObject::factory ('users'); // & instead?
if (PEAR::isError($obj)){
    die ($obj->getMessage ());
}

$user->find();

// Print the DataGrid
$dg = new Structures_DataGrid(10, $_REQUEST['page']);
while ($user->fetch()){
	$ar = $user->toArray();
    $rec = new Structures_DataGrid_Record($ar);
    $dg->addRecord($rec);
}

// tewak colours, etc
$rend =& $dg->getRenderer();
$rend->setTableEvenRowAttributes(array('bgcolor' => "#CCCCC"));
$rend->setTableHeaderAttributes(array('bgcolor'=>'#9999cc'));

$dg->render();

//TODO: fix the damn sliding/jumpping crap. >> << should jump x + num pages!
$pagestuff = $rend->getPaging();
print $pagestuff;

////KEEP EVERTHANG BELOW

?>
<!-- END DATAGRIDTEST -->


