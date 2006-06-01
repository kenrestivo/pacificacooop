<?php
 
//  Copyright (C) 2004-2006  ken restivo <ken@restivo.org>
// 
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
// 
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details. 
// 
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

// $Id$

// export the springfest invitations for doing a mailmerge
// this will be bitrotted soon, to be replaced by civicrm


require_once('../includes/first.inc');
require_once "COOP/Page.php";
require_once "COOP/View.php";
require_once "Spreadsheet/Excel/Writer.php";

/// XXX EVIL HACK!!! I *MUST* HARDCODE this debug to 4,
/// otherwise the whole damn thing fails on nfsn. works fine here of course
/// bizarre
$cp = new CoopPage(4);
$cp->logIn();
$cp->content_type = 'application/vnd.ms-excel'; // irrelevant, just NOT html!


$co =  new CoopView(&$cp, 'invitations', &$none);
$leads =  new CoopObject(&$cp, 'leads', &$co);



// Create an instance
$xls =& new Spreadsheet_Excel_Writer();
$xls->setTempDir('logs');



// Send HTTP headers to tell the browser what's coming
$xls->send("springfestinvitations.xls");

// Add a worksheet to the file, returning an object to add data to
$co->schoolYearChooser(); // fetch it from last?
$sheet =& $xls->addWorksheet('Invitations');

$title= sprintf('Springfest Invitations for %s exported %s by %s',
                $co->getChosenSchoolYear(),
                date("l F dS, Y h:i:s A"),
                $co->page->userStruct['username']);
$sheet->setFooter($title);
$sheet->setLandscape();



 
$co->obj->selectAdd('invitations.lead_id as response_code');

$co->obj->fb_fieldsToUnRender = array('lead_id');


$co->protectedJoin($leads, 'left');

// SHOW ONLY UNSENT
$co->obj->whereAdd('(label_printed is null or label_printed < "1000-01-01")');

$co->obj->orderBy('last_name, first_name, company');

$co->obj->preDefOrder = array( 'response_code', 'salutation',
                               'first_name','last_name',  
                               'title','company',
                               'address1', 'address2', 'city', 'state',
                               'zip', 'country');

// heh
$co->obj->fb_fieldLabels = $leads->obj->fb_fieldLabels;
$co->obj->fb_fieldLabels['response_code'] = 'Response Code';

$co->find();

// formatting
// 1st Argument - vertical split position
// 2st Argument - horizontal split position (0 = no horizontal split)
// 3st Argument - topmost visible row below the vertical split
// 4th Argument - leftmost visible column after the horizontal split
$sheet->freezePanes(array(3,1,4,2));


$i = 0;
$sheet->write($i++,1,$title); // so i have it somewhere
$sheet->write($i++,1,'ONLY UNPRINTED invitation labels shown here. Labels already printed once will not export again.'); // so i have it somewhere
while($co->obj->fetch()){
    //titles
    if($i < 3){
        $header = $co->makeHeader();
        $sheet->writeRow($i++,0,$header['titles']);
    }
    $sheet->writeRow($i,0,$co->toArray());
    $i++;
}

// Finish the spreadsheet, dumping it to the browser
$xls->close();
?>
