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
require_once('CoopView.php');
require_once "HTML/Template/PHPTAL.php";
require_once "lib/phptal_filters.php";
require_once('CoopIterator.php');  // XXX hack, around problems on nfsn


class CoopTALPage extends coopPage
{
    var $template; // reference to phptal template
    
    
    function build()
        {
            // virtual function. put your stuff in subclass of this.
            $template = new PHPTAL('sometemplate.xhtml');
        }


    function run()
        {
            // got to login before anything makes sense
            $this->logIn();

            $this->build();

            // NOTE: if this ref is unavailable,
            // the whole page fails except done()
            $this->template->setRef('page', $this);
        
            $this->template->addOutputFilter(new XML_to_HTML());
            
            if(headers_sent($file, $line)){
                PEAR::raiseError("headers sent at $file $line ", 666);
            }
            print  $this->template->execute();
            $this->finalDebug();

        }



} // END COOPREPORT


?>