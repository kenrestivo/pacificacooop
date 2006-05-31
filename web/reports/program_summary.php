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


require_once('../first.inc');
require_once('CoopTALPage.php');

class ProgramSummary extends CoopTALPage
{
    var $template_file = 'program_summary.xhtml';

    // specific to this page. when i dispatch with REST, i'll need several
    function build()
        {

            $this->title = 'Springfest Program Export';

            $sp =& new CoopView(&$this, 'sponsorships', &$none);
            $spons_struct =  $sp->obj->public_sponsors_structure(&$sp);
            $this->template->setRef('sponsors', $spons_struct);

            $inkind =& new CoopObject(&$this, 'in_kind_donations',
                                      &$none);
            $donors =  $inkind->obj->public_donors_structure(&$inkind);
            $this->template->setRef('donors', $donors);


            $pacs = array();
            foreach (array('Live', 'Silent') as $ptype){
                $pacs[$ptype] =& new CoopView(&$this, 'packages', &$none);
                $pt =& new CoopView(&$this, 'package_types', &$pacs[$ptype]);
                $pacs[$ptype]->protectedJoin($pt);
                $pacs[$ptype]->obj->whereAdd(
                    sprintf('package_type_short = "%s"', $ptype));
                array_push($pacs[$ptype]->obj->fb_preDefOrder, 'package_type_short');
                $pacs[$ptype]->obj->fb_fieldLabels['package_type_short'] = 'Package Type';
                $pacs[$ptype]->find(true);
                $pacs[$ptype]->fullText= 1; // gotta have it
            }

            $this->template->setRef('packagetypes', $pacs);


            $ads =& new CoopView(&$this, 'ads', 
                                   &$none);
            $ads->fullText = 1; // making their lives easier
            $ads = $ads->simpleTable();
            $this->template->setRef('ads', $ads);

        }
}


$r =& new ProgramSummary($debug);
$r->run();


?>