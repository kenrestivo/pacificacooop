<?php 
//$Id$

require_once("first.inc");
require_once("shared.inc");
require_once("auth.inc");

require_once("roster.inc");

require_once('DB/DataObject.php');
require_once("HTML/Table.php");

require_once('object-config.php');


print "<HTML>
		<HEAD>
			<TITLE>Data Entry</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School Data Entry</h2>
	";

// common code, abstract out
if (!isset ($_GET['table'])){
	die ('specify table');
}

$table = $_GET['table'];

$obj = DB_DataObject::factory ($table);
if (PEAR::isError($obj)){
	die ($obj->getMessage ());
}

// end common

// most have only one key. feel around for primary if not
$keys = $obj->keys ();
if (is_array ($keys)){
	$primaryKey = $keys[0];
}

// wtf is this, actually?? a smart cool thing no doubt
$titlefield = $_DB_DATAOBJECT_FORMBUILDER['CONFIG']['select_display_field'];

if (isset ($obj->select_display_field)){
	$titlefield = $obj->select_display_field;
}

$obj->find();

$tab =& new HTML_Table();


while ($obj->fetch()){
	$ar = array_values($obj->toArray());
	array_push($ar, 
			   sprintf ('<a href="details.php?id=%s&table=%s">Edit</a><br>',
   		  $obj->$primaryKey, $table));
	//yay! i updated my config.php, and now $obj->$titlefield works!
	
	$tab->addRow($ar);

	//print_r($obj->toArray());

//	print_r ($obj);
//oh, this so fuckign rules
	
}

echo '<hr><a href="details.php?table=' . $table . '">Add new</a>';

$tab->display();

print "<hr><br>";

done();


////KEEP EVERTHANG BELOW

?>
<!-- END TEST -->


 