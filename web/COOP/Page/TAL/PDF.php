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
require_once('CoopIterator.php');  // XXX cowardly not including it in CoopPage
require_once('lib/pdml.php');
require_once('lib/fpdf.php');


class CoopPDF extends coopPage
{
    var $fpdf; // reference to fpdf object
    
    function build()
        {
            // virtual function.
            //subclass should create the thing here. stuff it in $this->fpdf
        }


    function run()
        {
            $this->logIn();

            $this->build();

            $this->fpdf->Output();

            $this->finalDebug();

        }



} // END COOPPDF


?>