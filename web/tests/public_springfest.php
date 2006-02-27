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

require_once('CoopTALPage.php');

class BidSheetReport extends CoopTALPage
{
    var $template_file = 'springfest-microsite-shell.xhtml';

    function build()
        {
    ///////////// handle the no-year-equals-this-year navigation
    // must do this before choosign template
    $path = explode('/', $_SERVER['PATH_INFO']);
    if(preg_match('/^\d{4}$/', $path[1])){
        $sy = $path[1];
        $nav = $path[2];
    } else {
        $nav = $path[1];
        list($nothing, $sy) = explode('-', $this->currentSchoolYear);
    }
    // bah! gotta put it in vars, because that's where view fishes it out of
    $this->vars['last']['chosenSchoolYear'] = sprintf('%d-%d', $sy -1, $sy);


    $this->title = 'Springfest ' . $sy;





    
    /// TODO: move this to the database!! let user change names, add/remove
    $menu = array('home' => array('class' => 'nav',
                                  'content' => 'Overview'),
                  'event' => array('class' => 'nav',
                                   'content' => 'Where and When'),
                  'sponsorship' => array('class' => 'nav',
                                         'content' => 'Sponsorship'),
                  'auction' => array('class' => 'nav',
                                     'content' => 'Auction'),
                  'raffle' => array('class' => 'nav',
                                    'content' => 'Surfboard Raffle'),
                  'about' => array('class' => 'nav',
                                   'content' => 'About Us')
        );


    /// XXX miserable hack, there has to be a better way to do it in pure CSS
    $css = '<style type="text/css">';
    $menu_width = 100.0/count(array_keys($menu));
    foreach(array('a.nav:link', 'a.nav:visited', 'a.nav:hover', 
                  'a.nav:active', 'a.navcurrent') as $selector)
    {
        $css .= sprintf('%s { width: %0.2f%% } ', 
                        $selector, $menu_width);
    }
    $css .= '</style>';
    $this->template->setRef('extra_css', $css);
    

    /////////////// set current nav
    if(in_array($nav, array_keys($menu))){
        $menu[$nav]['class'] = 'navcurrent';
    } else {
        $menu['home']['class'] = 'navcurrent';
    }
    
    $this->template->setRef('nav', $menu);


    ///TODO: put in the stuff from public_auction here

    $this->printDebug("sy $sy nav $nav ". $families->getChosenSchoolYear(), 1);

 }
}


//////// MAIN
$r =& new PublicSpringfest($debug);
$r->run();



?>