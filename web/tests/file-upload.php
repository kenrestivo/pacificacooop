
<?php

//$Id$

/*
	Copyright (C) 2004  ken restivo <ken@restivo.org>
	 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details. 
	
	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


require_once("CoopPage.php");
require_once("CoopMenu.php");
require_once "HTML/QuickForm.php";
require_once("members.inc");
require_once("everything.inc");


$cp =& new CoopPage();
$cp->pageTop();
$menu =& new CoopMenu;
$menu->createLegacy(&$cp);

print $menu->topNavigation();
print "\n<hr>\n"; //ok, we're logged in. show the rest of the page


////////////// ok, now the page

$uploadForm = new HTML_QuickForm('upload_form', 'post');
$uploadForm->setMaxFileSize(8388608); // 8MB s/b as big as i need
$file =& $uploadForm->addElement('file', 'filename', 'File:');
$uploadForm->addElement('html', thruAuth($cp->auth, 1));
$uploadForm->addRule('filename', 'You must select a file', 'uploadedfile');
$uploadForm->addElement('submit', 'btnUpload', 'Upload');
if ($uploadForm->validate()) {
    $uploadForm->process('process', true);
}
else {
    $uploadForm->display();
}

function process($values) 
{
    global $file;
    $path = "../files"; 

    print"<pre>";
    print_r($file);
    print_r($values);
    print"</pre>";

    $unique_filename = sprintf("%d-%s", rand(1,200), 
                               $values['filename']['name']);
    if ($file->isUploadedFile()) {
        $file->moveUploadedFile($path, $unique_filename);
        print "file uploaded!";
    }
    else {
        print "No file uploaded";
    }
}

done();

?>
