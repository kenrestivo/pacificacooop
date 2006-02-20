<?php

	#  Copyright (C) 2004-2006  ken restivo <ken@restivo.org>
	# 
	#  This program is free software; you can redistribute it and/or modify
	#  it under the terms of the GNU General Public License as published by
	#  the Free Software Foundation; either version 2 of the License, or
	#  (at your option) any later version.
	# 
	#  This program is distributed in the hope that it will be useful,
	#  but WITHOUT ANY WARRANTY; without even the implied warranty of
	#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	#  GNU General Public License for more details. 
	# 
	#  You should have received a copy of the GNU General Public License
	#  along with this program; if not, write to the Free Software
	#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

//$Id$

require_once('CoopPage.php');
require_once('CoopNewDispatcher.php');
require_once "HTML/Template/PHPTAL.php";
require_once "lib/phptal_filters.php";
require_once('CoopIterator.php');  // XXX hack, around problems on nfsn



// specific to this page. when i dispatch with REST, i'll need several
function &build(&$page)
{

    // let the template know all about it
    $template = new PHPTAL('bid-sheet.xhtml');


    ////////////// object time. PLACEHOLDER
    $packages =& new CoopView(&$page, 'packages', &$nothing);
    $pt =& new CoopView(&$page, 'package_types', &$packages);
    $packages->protectedJoin($pt);
    $packages->obj->whereAdd('(package_type_short = "Silent" or package_type_short = "Live")');
    // tal needs this to decide whether to print the increment
    array_push($packages->obj->fb_preDefOrder, 'package_type_short');
    $packages->obj->fb_fieldLabels['package_type_short'] = 'Package Type';
    

    $packages->fullText= 1; // gotta have it

    if(devSite()){
        //$packages->obj->limit(10); // XXX HACK FOR TESTING
    }

    $packages->find(true);
    $template->setRef('packages', $packages);

    $page->printDebug("sy $sy nav $nav ". $packages->getChosenSchoolYear(), 1);


    return $template;
}


//////// MAIN
$cp =& new coopPage( $debug);


// got to RUN certain things before anything makes sense
$cp->logIn();


$template =& build(&$cp);

// NOTE: if this ref is unavailable, the whole page fails except done()
$template->setRef('page', $cp);


//confessObj($template->getContext(), 'context');


$template->addOutputFilter(new XML_to_HTML());

if(headers_sent($file, $line)){
    PEAR::raiseError("headers sent at $file $line ", 666);
}
print  $template->execute();
$cp->finalDebug();


?>