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
	// returns the type. or false if no update was needed
	// WEIRD, shouldn't it return lastinsertid?
	function updateSponsorships($id, $idname)
		{
			$typeid= $this->calculateSponsorshipType($id, $idname);

			//anything already there?
			$sp =& new CoopObject(&$this->cp, 'sponsorships', &$nothing);
			//$sp->obj->debugLevel(2);
			$sp->obj->school_year = $this->schoolYear;
			$sp->obj->$idname = $id;
			//NOTE: do *not* search for typeid! i must match ones that changed!
			$found =$sp->obj->find(); 
			if($found){
				$sp->obj->fetch();
				// if there's no manual override there, change it to match calc
				if($sp->obj->entry_type == 'Automatic'){
					if($typeid > 0){
						if($typeid == $sp->obj->sponsorship_type_id){
							return false;
						}
						$sp->obj->sponsorship_type_id = $typeid;
						$sp->obj->update();
					} else {
						// or delete it if they don't quailfy anymore
						$hack =& new CoopObject(&$this->cp, 'sponsorships', 
												&$nothing);
						$hack->obj->{$hack->pk} = $sp->obj->{$hack->pk};
						$hack->obj->delete();
					}
				}
				return $typeid;
			}
							
			if($typeid > 0){
				// otherwise save a new one
				$sp =& new CoopObject(&$this->cp, 'sponsorships', &$nothing);
				$sp->obj->school_year = $this->schoolYear;
				$sp->obj->$idname = $id;
				$sp->obj->sponsorship_type_id = $typeid;
				$sp->obj->insert();
				return $typeid;
			}
		}
	
	// checks activity for this entity, and returns sponsorlevel
	function calculateSponsorshipType($id, $idname)
		{
			$hack = array('company_id' => 'findSolicitSponsors',
						  'lead_id' => 'findLeadSponsors');
			return $this->$hack[$idname]($id);
		}


	function findSolicitSponsors($id)
		{
			// i curse the very day that i agreed to do this goddamned project
			$co =& new CoopObject(&$this->cp, 'companies', &$nothing);
			//$co->obj->debugLevel(2);
			$co->obj->query(sprintf("
    select  sum(payment_amount) as payment_amount
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '%s'
				and cinj.company_id = %d
        group by cinj.company_id",
									$this->schoolYear,  $id));
			$co->obj->fetch(); // there can be, only one
				
			if($co->obj->payment_amount > 0){
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


	function findLeadSponsors($id)
		{
			// i curse the very day that i agreed to do this goddamned project
			$co =& new CoopObject(&$this->cp, 'leads', &$nothing);
			//$co->obj->debugLevel(2);
			$query = sprintf("select leads.lead_id,
        coalesce(sum(tic.total),0) + coalesce(sum(inc.total),0) 
				as payment_amount
from leads
left join 
    (select lead_id, sum(payment_amount) as total
     from leads_income_join as linj
     left join income 
              on linj.income_id = 
                income.income_id
        where income.school_year = '%s'
        group by linj.lead_id) 
    as inc
        on leads.lead_id = inc.lead_id
left join 
    (select lead_id, sum(payment_amount) as total
     from tickets
     left join income 
              on tickets.income_id = 
                income.income_id
        where income.school_year = '%s'
        group by tickets.lead_id) 
    as tic
        on tic.lead_id = leads.lead_id
group by leads.lead_id
having payment_amount > 0 and leads.lead_id = %d
order by payment_amount desc
        ",
							 $this->schoolYear, $this->schoolYear, $id);

			$co->obj->query($query);

		$co->obj->fetch(); // there can be, only one
				
			if($co->obj->payment_amount){
				//print "GOT ONE $id " . $co->obj->payment_amount;
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


