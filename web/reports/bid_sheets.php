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

    $page->title = 'Bid Sheets and Gift Certificates';

    ////////////// BIDSHEETS
    $bidsheets =& new CoopView(&$page, 'packages', &$nothing);
    $pt =& new CoopView(&$page, 'package_types', &$bidsheets);
    $bidsheets->protectedJoin($pt);
    $bidsheets->obj->whereAdd('package_type_short = "Silent"');
    // tal needs this to decide whether to print the increment
    array_push($bidsheets->obj->fb_preDefOrder, 'package_type_short');
    $bidsheets->obj->fb_fieldLabels['package_type_short'] = 'Package Type';
    

    $bidsheets->fullText= 1; // gotta have it

    if(devSite() && $_REQUEST['limit']){
         // XXX TEMPORARY HACK FOR TESTING
        $bidsheets->obj->limit($_REQUEST['limit']);
    }

    $bidsheets->find(true);
    $template->setRef('bidsheets', $bidsheets);

    $page->printDebug("sy $sy nav $nav ". $bidsheets->getChosenSchoolYear(), 1);


    ////////////// GIFT CERTIFICATES 
    $giftcerts =& new CoopView(&$page, 'packages', &$nothing);
    $pt =& new CoopView(&$page, 'package_types', &$giftcerts);
    $giftcerts->protectedJoin($pt);
    $giftcerts->obj->whereAdd('item_type = "Gift Certificate"');

    $giftcerts->fullText= 1; // gotta have it

    if(devSite() && $_REQUEST['limit']){
         // XXX TEMPORARY HACK FOR TESTING
        $giftcerts->obj->limit($_REQUEST['limit']);
    }

    $giftcerts->find(true);
    $template->setRef('giftcerts', $giftcerts);

    // simple year.
    list($crap, $year) = explode('-', $giftcerts->getChosenSchoolYear());
    $year = 'Springfest ' . $year;
    $template->setRef('eventdate', $year);
    

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