<?php

//$Id$

// unit test for my vitally required includes. return an error if
// something has gone horribly wrong

// so that it will presumably work from anywhere!
require_once('../includes/first.inc');


require_once "HTML/QuickForm.php";
require_once "HTML/QuickForm/group.php";
require_once "HTML/Table.php";
require_once('HTML/TreeMenu.php');
require_once('DB/DataObject.php');
require_once('Mail.php');
require_once('Text/Diff.php');
require_once "Spreadsheet/Excel/Writer.php";
require_once "HTML/Template/PHPTAL.php";
require_once('Types/Iterator.php');
require_once('Types/Ref.php');

require_once('COOP/Page.php');
require_once('COOP/View.php');
require_once('COOP/Form.php');
require_once('COOP/Menu.php');


$cp =& new CoopPage($debug);
$yech =& new CoopObject(&$cp, 'families', &$none);


//TODO: check for lib/tiny_mce/tiny_mce.js , which is NOT in my cvs!
//and is required for the app to run


//require_once('FAIL-NOW.php'); // make sure it fails too ;-)

print "OK";

?>