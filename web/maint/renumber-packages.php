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

// the package numbers can get all screwed up easily.
// this script fixes that. it shouldnt' be necessary, but it is


require_once('../includes/first.inc');
require_once('COOP/Page.php');
require_once('COOP/View.php');



//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();


$atd = new CoopView(&$cp, 'packages', $none);

print $cp->topNavigation();
//XXX BROKEN! path! print $cp->stackPath();

//TODO: force back to main menu

print "\n<hr /></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div class="centerCol">';

print '<h3>Renumbering packages</h3>';


function viewHack(&$atd)
{
        $ptype =& new CoopObject(&$atd->page, 'package_types', &$atd);
        $ptype->obj->find();
        while($ptype->obj->fetch()) {   
            print "Renumbering {$ptype->obj->package_type_short}...<br />";
            $pack =& new CoopObject(&$atd->page, 'packages', &$atd);
            $pack->obj->query('set @num=0'); // makes me nervous
            $pack->obj->query(
                sprintf(
                    'update %s set package_number=(@num:=@num+1) 
where package_type_id = %d and school_year = "%s"
ORDER BY package_number',
                    $pack->obj->__table,
                    $ptype->obj->package_type_id,
                    $pack->getChosenSchoolYear()));
        }

        print '<br /><p>Packages renumbered!</p>';
        print $atd->page->selfURL(
            array('value' => 'Back to Packages',
                  'base' => COOP_ABSOLUTE_URL_PATH . '/pages/generic.php',
                  'inside' => array('action' => 'view',
                                    'table' => 'packages')));

}

// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
	 
//// EDIT AND NEW //////
 case 'new':
 case 'edit':
	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$atd);

	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


