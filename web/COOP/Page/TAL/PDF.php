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

require_once('CoopTALPage.php');
require_once('lib/pdml.php');
require_once('lib/fpdf.php');


class CoopPDF extends CoopTALPage
{
    var $fpdf; // reference to pdml object (subclass of fpdf)
    var $content_type = 'application/pdf;charset=utf-8';
    
    function prepare()
        {
            parent::prepare();
            $this->fpdf = new PDML('P','pt','Letter'); 

        }

    function output()
        {
            $pdml = $this->template->execute();

            //TODO: if debuglevel > something, dump the tal'ed pdml first!

            $this->fpdf->ParsePDML($pdml);

            $this->fpdf->Output();
        }


} // END COOPPDF


?>