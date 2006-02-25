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

class ProgramSummary extends CoopReport
{

// specific to this page. when i dispatch with REST, i'll need several
    function build()
        {

            // let the template know all about it
            $this->template = new PHPTAL('program_summary.xhtml');

            $this->page->title = 'Springfest Program Export';

            $sp =& new CoopView(&$this->page, 'sponsorships', &$none);
            $spons_struct =  $sp->obj->public_sponsors_structure(&$sp);
            $this->template->setRef('sponsors', $spons_struct);

            $inkind =& new CoopObject(&$this->page, 'in_kind_donations',
                                      &$none);
            $donors =  $inkind->obj->public_donors_structure(&$inkind);
            $this->template->setRef('donors', $donors);


            $pac =& new CoopView(&$this->page, 'packages', &$none);
            $pt =& new CoopView(&$this->page, 'package_types', &$pac);
            $pac->protectedJoin($pt);
            $pac->obj->whereAdd('(package_type_short = "Live" or package_type_short = "Silent")');
            array_push($pac->obj->fb_preDefOrder, 'package_type_short');
            $pac->obj->fb_fieldLabels['package_type_short'] = 'Package Type';

            $pac->find(true);
            $pac->fullText= 1; // gotta have it

            $this->template->setRef('packages', $pac);

            return; //XXX FOR DEBUG ONLY!


            $ads =& new CoopView(&$this->page, 'ads', 
                                   &$none);
            $ads = $ads->simpleTable();
            $this->template->setRef('ads', $ads);

        }
}


$r =& new ProgramSummary($debug);
$r->run();


?>