<?php 

//$Id$

/*
	Copyright (C) 2004,2005  ken restivo <ken@restivo.org>
	 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details. 
	
	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once('CoopObject.php');
require_once('DB/DataObject.php');
require_once('object-config.php');
require_once('DB/DataObject/Cast.php');




//////////////////////////////////////////
/////////////////////// SPONSORSHIP CLASS
class Sponsorship
{
	var $cp ;  // alias to coop page object
	var $sponsorTypes = array(); // cache of sponsortypes for this year
	var $schoolYear; // cache of schoolyear
	
	function Sponsorship(&$cp, $schoolyear = false)
		{
			if(!is_object($cp)){
				PEAR::raiseError('must pass coop object in', 888);
			}
			$this->cp = $cp;
			$this->schoolYear = $schoolyear ? $schoolyear : findSchoolYear();
			$this->getSponsorTypes();
		}
		


		// updates or enters their sponsorships
	function updateSponsorships($id, $idname)
		{
			$typeid= $this->calculateSponsorship($id, $idname);

			//anything already there?
			$sp =& new CoopObject(&$this->cp, 'sponsorships', &$nothing);
			$sp->obj->school_year = $this->schoolYear;
			$sp->obj->$idname = $id;
			if($sp->obj->find(true)){
				// if there's no manual override there, change it to match calc
				if($sp->obj->entry_type == 'Automatic'){
					if($typeid){
						$sp->obj->sponsorship_type_id = $typeid;
						$sp->obj->update();
					} else {
						// or delete it if they don't quailfy anymore
						$sp->obj->delete();
					}
				}
				return;
			}
							
			// otherwise save a new one
			$sp =& new CoopObject(&$this->cp, 'sponsorships', &$nothing);
			$sp->obj->school_year = $this->schoolYear;
			$sp->obj->$idname = $id;
			$sp->obj->sponsorship_type_id = $typeid;
			$sp->obj->insert();
		}
	
	// checks activity for this entity, and returns sponsorlevel
	function calculateSponsorshipType($id, $idname)
		{
			// TODO check company/lead here, like in thankyou
			$hack = array(
				'company_id' => array(
					'table' => 'companies',
					'join' => 'companies_income_join'
					),
				'lead_id' => array(
					'table' => 'leads',
					'join' => 'leads_income_join'
					));

			// i curse the very day that i agreed to do this goddamned project
			$co =& new CoopObject(&$this->cp, $hack[$idname]['table'], 
								  &$nothing);
			$co->obj->debug(2);
			$co->obj->query(sprintf("
    select  sum(payment_amount) as payment_amount
     from %s as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '%s'
				and cinj.%s = %d
        group by cinj.%s",
									$hack[$idname]['join'],
									$this->schoolYear, 
									$idname, $id, $idname));
			$co->obj->fetch(); // there can be, only one
				
			if($co->obj->payment_amount){
				// there's money there, check its sponsorship level
				foreach($this->sponsorTypes as $typeid => $amt){
					if($co->obj->payment_amount >= $amt){
						return $typeid;
					}
				}
			} 

			// there's no money,  no dice
			return false;
		}


	function getSponsorTypes()
		{
				$sp =& new CoopObject(&$this->cp, 'sponsorship_types', 
									  &$nothing);
				$sp->obj->school_year = $this->schoolYear;
				$sp->obj->orderBy('sponsorship_price desc');
				$sp->obj->find();
				while($sp->obj->fetch()){
					$this->sponsorTypes[$sp->obj->sponsorship_type_id] = 
						$sp->obj->sponsorship_price;
				}
		}


} // END SPONSORSHIP CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END SPONSORSHIPCLASS -->


