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

chdir('../');

require_once('CoopPage.php');
require_once('CoopNewDispatcher.php');
require_once "HTML/Template/PHPTAL.php";
require_once "lib/phptal_filters.php";
require_once('CoopIterator.php');  // XXX hack, around problems on nfsn


// specific to this page. when i dispatch with REST, i'll need several
function &build(&$page)
{
    // let the template know all about it
    $template = new PHPTAL('springfest-microsite-shell.xhtml');

    
    /// menu
    $menu = array('home' => array('class' => 'nav',
                                  'content' => 'Springfest Home'),
                  'event' => array('class' => 'nav',
                                   'content' => 'Where and When'),
                  'sponsorship' => array('class' => 'nav',
                                         'content' => 'Sponsorship'),
                  'auction' => array('class' => 'nav',
                                     'content' => 'Auction'),
                  'raffle' => array('class' => 'nav',
                                    'content' => 'Raffle'),
                  'about' => array('class' => 'nav',
                                   'content' => 'About Us')
        );

    // handle the no-year-equals-this-year navigation
    $path = explode('/', $_SERVER['PATH_INFO']);
    if(preg_match('/^\d{4}$/', $path[1])){
        $sy = $path[1];
        $nav = $path[2];
    } else {
        $nav = $path[1];
        list($nothing, $sy) = explode('-', $page->currentSchoolYear);
    }

    // make current nav
    if(in_array($nav, array_keys($menu))){
        $menu[$nav]['class'] = 'navcurrent';
    } else {
        $menu['home']['class'] = 'navcurrent';
    }
    
    $template->setRef('nav', $menu);

    // object time
    $families =& new CoopView(&$page, 'families', 
                                    &$nothing);
    $families->chosenSchoolYear = sprintf('%d-%d', $sy -1, $sy);
    $families->find(true);
    $page->title = 'Springfest ' . $sy;
    $template->setRef('families', $families);


    $page->printDebug("sy $sy nav $nav", 1);

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