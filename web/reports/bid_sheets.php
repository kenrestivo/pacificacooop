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

require_once('CoopReport.php');

class BidSheetReport extends CoopReport
{

// specific to this page. when i dispatch with REST, i'll need several
    function build()
        {

            // let the template know all about it
            $this->template = new PHPTAL('bid-sheet.xhtml');

            $this->page->title = 'Springfest Packaging Bid Sheets';

            ////////////// BIDSHEETS
            $bidsheets =& new CoopView(&$this->page, 'packages', &$nothing);
            $pt =& new CoopView(&$this->page, 'package_types', &$bidsheets);
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
            $this->template->setRef('bidsheets', $bidsheets);

            $this->page->printDebug("sy $sy nav $nav ". $bidsheets->getChosenSchoolYear(), 1);


            $crap = array_fill(0,10,'');
            $this->template->setRef('blanklines', $crap);

            
        }
}


$r =& new BidSheetReport($debug);
$r->run();


?>