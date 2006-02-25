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

            $sp =& new CoopObject(&$this->page, 'sponsorships', &$none);
            $spons =  $sp->obj->public_sponsors(
                &$this->page,
                $this->page->currentSchoolYear);
            $this->template->setRef('sponsors', $spons);

            $inkind =& new CoopObject(&$this->page, 'in_kind_donations',
                                      &$none);
            $donors =  $inkind->obj->public_donors(
                &$this->page,
                $this->page->currentSchoolYear);
            $this->template->setRef('donors', $donors);


            $pac =& new CoopView(&$this->page, 'packages', 
                                   &$none);
            $packages = $pac->simpleTable();
            $this->template->setRef('packages', $packages);

            $ads =& new CoopView(&$this->page, 'ads', 
                                   &$none);
            $ads = $ads->simpleTable();
            $this->template->setRef('ads', $ads);

        }
}


$r =& new ProgramSummary($debug);
$r->run();


?>