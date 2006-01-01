<?php

	#  Copyright (C) 2004-2005  ken restivo <ken@restivo.org>
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



///// move this to its own class and generalise it?
class ReportDispatcher extends CoopNewDispatcher
{

    function view()
        {
            // create a new template object
            if(!$this->page->auth['uid']){
                PEAR::raiseError('not logged in, or bad reference', 666);
            }
            return "foo!";
            
            $fam =& new CoopView(&$this->page, 'families', &$nothing);
            $fam->find(true);
            
            $context = array('families' => &$fam);

        }

}


//////// MAIN
$cp =& new coopPage( $debug);


$template = new PHPTAL("outershell-templ.xhtml");


// ok, the inside!
$cp->context['dispatcher'] =& new ReportDispatcher(&$cp);


$template->setAll(&$cp->context);
$template->addOutputFilter(new XML_to_HTML());

if(headers_sent($file, $line)){
    PEAR::raiseError("headers sent at $file $line ", 666);
}
print  $template->execute();
$cp->finalDebug();



?>