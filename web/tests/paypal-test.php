<?php 
//$Id$

require_once("first.inc");
require_once("shared.inc");
require_once("auth.inc");

require_once("roster.inc");

require_once('DB/DataObject.php');
require_once("HTML/Table.php");
require_once 'HTML/QuickForm.php';
require_once('DB/DataObject/FormBuilder.php');

require_once('object-config.php');


print "<HTML>
		<HEAD>
			<TITLE>Data Entry</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School Data Entry</h2>
	";


$pv = $_POST ? $_POST : $_GET;

//DB_DataObject::debugLevel(5);

if (!isset ($pv['table'])){
	$form = new HTML_QuickForm('gettable', 'get');
	$grp[] =& HTML_QuickForm::createElement(
		'text', 'table', 'Browse table:');
	$grp[] =& HTML_QuickForm::createElement('submit', null, 'Send');
	$form->addGroup($grp, NULL, 'Browse table:');
	$form->display();
	done();
}

$table = $pv['table'];

$obj = DB_DataObject::factory ($table);
if (PEAR::isError($obj)){
	die ($obj->getMessage ());
}


////////////// the detail form
if($pv['action'] == 'detail'){

	if (isset ($pv['id'])){
		$obj->get ($pv['id']);
	}


//print_r($obj);

//now the good stuff
	$build =& DB_DataObject_FormBuilder::create ($obj);
	$form = $build->getForm ($_SERVER['PHP_SELF']);
	$form->addElement('hidden', 'action', 'detail');
	if($form->validate ()){
		$res = $form->process (array ($build, 'processForm'), false);
		if ($res){
			$obj->debug('processed successfully', 'detailform', 0);
		 	$action = $form->getElement('action');
			$action->setValue('list');
		}
		echo "aaauugh!<br>";
	}

	$form->display();
 
	done();
}

////////////////////////////
////////// the list table
////////////////////////////


// most have only one key. feel around for primary if not
$keys = $obj->keys ();
if (is_array ($keys)){
	$primaryKey = $keys[0];
}

// handle default display field
$titlefield = $_DB_DATAOBJECT_FORMBUILDER['CONFIG']['select_display_field'];
if (isset ($obj->select_display_field)){
	$titlefield = $obj->select_display_field;
}

$obj->find();

$tab =& new HTML_Table();

$hdr = 0;
while ($obj->fetch()){
	$ar = array_values($obj->toArray());
	array_push($ar, 
			   sprintf('<a href="%s?action=detail&id=%s&table=%s">
						Edit</a><br>',
   		  $_SERVER['PHP_SELF'], $obj->$primaryKey, $table));
	//yay! i updated my config.php, and now $obj->$titlefield works!

	if($hdr++ < 1){
		$tab->addRow(array_keys($obj->toArray()));
	}
	$tab->addRow($ar);

	//print_r($obj->toArray());

//	print_r ($obj);
//oh, this so fuckign rules
	
}

printf('<hr><a href="%s?action=detail&table=%s">Add new</a>', 
	   $_SERVER['PHP_SELF'], $table) ;

$tab->display();

print "<hr><br>";

done();


////KEEP EVERTHANG BELOW

?>
<!-- END TEST -->


 