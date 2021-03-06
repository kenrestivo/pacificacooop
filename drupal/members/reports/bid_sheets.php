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


require_once('../includes/first.inc');
require_once('COOP/Page/TAL/PDF.php');


class BidSheetReport extends CoopPDF
{

    var $template_file = 'bid-sheet.pdml';


    function build()
        {
            /// set some defaults
            $this->fpdf->AddFont('bernhard-modern');
            $this->fpdf->font_size = array('18');
            $this->fpdf->font_face = array('bernhard-modern');
           

            ////////////// BIDSHEETS
            $bidsheets =& new CoopView(&$this, 'packages', &$nothing);
            $pt =& new CoopView(&$this, 'package_types', &$bidsheets);
            $bidsheets->protectedJoin($pt);
            $bidsheets->obj->whereAdd('(package_type_short = "Silent" or package_type_short = "Live")');
            // tal needs this to decide whether to print the increment
            array_push($bidsheets->obj->fb_preDefOrder, 'package_type_short');
            $bidsheets->obj->fb_fieldLabels['package_type_short'] = 'Package Type';
    

            $bidsheets->fullText= 1; // gotta have it

            if(devSite() && $_REQUEST['limit']){
                // XXX TEMPORARY HACK FOR TESTING
                $bidsheets->obj->limit($_REQUEST['limit']);
            }

            $bidsheets->find(true);
            $this->template->setRef('bidsheets', $bidsheets);

            $this->printDebug("sy $sy ". $bidsheets->getChosenSchoolYear(), 1);


            $crap = array_fill(0,10,'');
            $this->template->setRef('blanklines', $crap);
            
        }
}


$r =& new BidSheetReport($debug);
$r->run();


?>