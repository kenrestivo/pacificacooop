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
require_once('lib/dbdo_iterator.php');



///// move this to its own class and generalise it?
class ReportDispatcher extends CoopNewDispatcher
{

    function view()
        {
            // create a new template object
            $template = new PHPTAL("attendance.html", 
                                   'templates', 'cache');
            
            $context = array('families' => array(
                                 array('name' => 'foo', 
                                       'family_id' => 222),
                                 array('name' => 'bar',
                                       'family_id' => 333)));

            
            $fam =& new CoopView(&$this->page, 'families', &$nothing);
            $fam->find(true);
            
            $it =& new DB_DataObjectIterator(&$fam->obj);
            $context = array('families' => $it);

            $template->setAll($context);
            
            // execute template
            return  $template->execute();
        }

}


//////// MAIN


$cp = new coopPage( $debug);
$cp->buffer($cp->pageTop());


$cp->buffer($cp->topNavigation());


$disp =& new ReportDispatcher(&$cp);


$cp->buffer(sprintf("<h3>%s</h3>",$atd->obj->fb_formHeaderText));
// NOT WORKING YET $cp->buffer($atd->titleJSHack());

$cp->buffer("\n<hr /></div><!-- end header div -->\n"); //ok, we're logged in. show the rest of the page
$cp->buffer('<div id="centerCol">');


$cp->buffer($disp->dispatch());


if(headers_sent($file, $line)){
    PEAR::raiseError("headers sent at $file $line ", 666);
}
print $cp->flushBuffer();

$cp->done();



?>