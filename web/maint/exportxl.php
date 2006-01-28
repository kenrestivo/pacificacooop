<?php

chdir('../');

require_once('first.inc');
require_once "CoopPage.php";
require_once "CoopView.php";
require_once "Spreadsheet/Excel/Writer.php";

$cp = new CoopPage();
$cp->logIn();
$cp->content_type = 'application/vnd.msexcel';

//PEAR::raiseError('wtf?', 555);
//$olderr = set_error_handler("errorHandler"); 


// Create an instance
$xls =& new Spreadsheet_Excel_Writer();
$xls->setTempDir('logs');



// Send HTTP headers to tell the browser what's coming
$xls->send("springfestinvitations.xls");

// Add a worksheet to the file, returning an object to add data to
$sheet =& $xls->addWorksheet('Springfest Invitations');



$co =  new CoopView(&$cp, 'invitations', &$none);
$leads =  new CoopObject(&$cp, 'leads', &$co);

 
$co->obj->selectAdd('invitations.lead_id as response_code');

$co->obj->fb_fieldsToUnRender = array('lead_id');


$co->protectedJoin($leads, 'left');

$co->obj->orderBy('last_name, first_name, company');

$co->obj->preDefOrder = array( 'response_code', 'salutation',
                               'last_name', 'first_name', 
                               'title','company',
                               'address1', 'address2', 'city', 'state',
                               'zip', 'country');

// heh
$co->obj->fb_fieldLabels = $leads->obj->fb_fieldLabels;
$co->obj->fb_fieldLabels['response_code'] = 'Response Code';

$co->find();

$i = 0;
while($co->obj->fetch()){
    $sheet->writeRow($i,0,$co->toArray());
    $i++;
}

// Finish the spreadsheet, dumping it to the browser
$xls->close();
?>
