<?php

chdir('../');

require_once('first.inc');
require_once "CoopPage.php";
require_once "Spreadsheet/Excel/Writer.php";

$cp = new CoopPage();


//PEAR::raiseError('wtf?', 555);
//$olderr = set_error_handler("errorHandler"); 


// Create an instance
$xls =& new Spreadsheet_Excel_Writer();
$xls->setTempDir('logs');

// Send HTTP headers to tell the browser what's coming
$xls->send("test.xls");

// Add a worksheet to the file, returning an object to add data to
$sheet =& $xls->addWorksheet('Binary Count');

// Write some numbers
for ( $i=0;$i<11;$i++ ) {
 // Use PHP's decbin() function to convert integer to binary
 $sheet->write($i,0,decbin($i));
}

// Finish the spreadsheet, dumping it to the browser
$xls->close();
?>
