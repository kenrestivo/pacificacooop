<?php

chdir('../');

require_once('first.inc');
require_once "CoopPage.php";
require_once "CoopView.php";
require_once "Spreadsheet/Excel/Writer.php";

$cp = new CoopPage();


//PEAR::raiseError('wtf?', 555);
//$olderr = set_error_handler("errorHandler"); 


// Create an instance
$xls =& new Spreadsheet_Excel_Writer();
$xls->setTempDir('logs');

// Send HTTP headers to tell the browser what's coming
$xls->send("springfestinvitations.xls");

// Add a worksheet to the file, returning an object to add data to
$sheet =& $xls->addWorksheet('Springfest Invitations');



$co =  new CoopObject(&$cp, 'invitations', &$none);
$leads =  new CoopObject(&$cp, 'leads', &$co);

$co->obj->selectAdd();
$co->obj->selectAdd($leads->obj->fb_labelQuery);
$co->obj->selectAdd('invitations.lead_id');

$co->protectedJoin($leads, 'left');

$co->obj->orderBy('last_name, first_name, company');

$co->obj->find();

$i = 0;
while($co->obj->fetch()){
    $sheet->write($i++,0,$co->obj->lead_label);
}

// Finish the spreadsheet, dumping it to the browser
$xls->close();
?>
