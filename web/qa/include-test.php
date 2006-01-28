<?php

//$Id$

// unit test for my vitally required includes. return an error if
// something has gone horribly wrong
chdir('../');
require_once('first.inc');
require_once "HTML/QuickForm.php";
require_once "HTML/QuickForm/group.php";
require_once "HTML/QuickForm/group.php";
require_once "HTML/Table.php";
require_once 'HTML/Menu/DirectRenderer.php';
require_once 'HTML/Menu/DirectTreeRenderer.php';
require_once("DB/DataObject.php");
require_once("HTML/Table.php");
require_once('DB/DataObject.php');
require_once('Pager/Pager.php');
require_once('Mail.php');
require_once('Text/Diff.php');
require_once "Spreadsheet/Excel/Writer.php";

//TODO: check for lib/tiny_mce/tiny_mce.js , which is NOT in my cvs!
//and is required for the app to run


//require_once('FAIL-NOW.php'); // make sure it fails too ;-)

print "OK";

?>