<?php 

//$Id$

/*
	Copyright (C) 2004  ken restivo <ken@restivo.org>
	 
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

define('COOP_FOUNDED', 1962);
define('FIRST_SPRINGFEST', 1972);


//////////////////////////////////////////
/////////////////////// THANKYOU CLASS
class ThankYou
{
	//TODO: put this in a file, and fopen it
	var $template = 
"[:DATE:]

[:NAME:]
[:ADDRESS:]

Dear [:NAME:],

We would like to thank you for your donation of [:ITEMS:] to our [:ITERATION:][:ORDINAL:] Annual Springfest [:YEAR:] Wine Tasting and Auction. Your contribution is greatly appreciated.

Your donation helps fund scholarship programs and improvements to our school.

For [:YEARS:] years, the Pacifica Co-op Nursery School has provided an enriching experience for both children and parents of our community. The Co-op's program enables parents to work together with a highly qualified staff to encourage physical, social, and emotional growth. The theme-based curriculum creates continuous opportunities for our children to enhance their self-esteem and strong sense of regard for others.

The Pacifica Co-op Nursery School is a non-profit, parent participation program. We rely on the assistance of the community in conjunction with friends and family to meet our ever-increasing budget. Again, we thank you for considering the Pacifica Co-op Nursery School a deserving place to offer your community support.

For your tax donation records, our tax-exempt I.D number is 94-1527749.

\"An investment in our children is an investment in our community.\"


Sincerely,

[:FROM:]

Pacifica Co-op Nursery School
548 Carmel Avenue
Pacifica, Ca 94044
650 355-3272
http://www.pacificacoop.org/
";

	function ThankYou($substitutions)
		{
			$this->substitutions = $substitutions;
			
			$sy = findSchoolYear();

			// set defaults if empty: date, schoolyear, etc
			if(!$this->substitutions['YEAR']){
				$tmp = explode('-', $sy);
				$this->substitutions['YEAR'] = $tmp[1];
			}

			if(!$this->substitutions['YEARS']){
				$this->substitutions['YEARS'] = 
				$this->substitutions['YEAR'] - COOP_FOUNDED;
			}

			if(!$this->substitutions['ITERATION']){
				$this->substitutions['ITERATION'] =
				$this->substitutions['YEAR'] - FIRST_SPRINGFEST;
			}
			if(!$this->substitutions['ORDINAL']){
				$this->substitutions['ORDINAL'] =
				$this->ordinal($this->substitutions['ITERATION']);
			}
			
			if(!$this->substitutions['DATE']){
				$this->substitutions['DATE'] = date('l, F j, Y');
			}
			
			// leave from blank if it's not there
				if (!$this->substitutions['FROM']){
					$this->substitutions['FROM'] = "";
				}

			
		}

	function toHTML()
		{

			$subst = $this->substitutions;

			//un-arrayify the ones that are arrays
			// and format them html-like
			$subst['ADDRESS'] = implode('<br>', $subst['ADDRESS']);
			$subst['ITEMS'] = implode(',', $subst['ITEMS']);
			$subst['FROM'] = sprintf('<br><br><br>%s', $subst['FROM']);
			$subst['ORDINAL'] = sprintf('<sup>%s</sup>', $subst['ORDINAL']);

			
	  			//confessObj($this, 'this');
			foreach(array_keys($subst) as $key){
				$from[] = sprintf('[:%s:]', $key);
			}
			$to = array_values($subst);

			$text .= '<div id="toplogo"><img src="/round-small-logo.gif"></div>';
	
			$text .= '<div id="mainletter"><p class="letter">';
			$text .= str_replace($from, $to, nl2br($this->template));
			$text .= '</p></div>';

			//TODO: hack for the "tagline" at the bottom. preg_match?
			// if it starts with a " and ends with a ", curly and bolditalic
		
			return $text;
		}

	//returns just the ordinal part
	function ordinal($number)
		{
			$lastdigit = substr($number, -1);
			switch ($lastdigit){
			case 1: 
				return "st";
				break;
				
			case 2: 
				return "nd";
				break;
				
			case 3: 
				return "rd";
				break;
				
			default:
				return "th";
				break;
			}
		}


	function toText()
		{

			$subst = $this->substitutions;

			//un-arrayify the ones that are arrays
			// and format them html-like
			$subst['ADDRESS'] = implode("\n", $subst['ADDRESS']);
			$subst['ITEMS'] = implode(',', $subst['ITEMS']);
			$subst['FROM'] = sprintf("\n\n\n%s", $subst['FROM']);
			$subst['ORDINAL'] = sprintf('%s', $subst['ORDINAL']);

			
	  			//confessObj($this, 'this');
			foreach(array_keys($subst) as $key){
				$from[] = sprintf('[:%s:]', $key);
			}
			$to = array_values($subst);

		
			$text .= str_replace($from, $to, $this->template);
		

			//TODO: hack for the "tagline" at the bottom. preg_match?
			// if it starts with a " and ends with a ", curly and bolditalic
		
			return $text;
		}



} // END THANK YOU CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END THANKYOUCLASS -->


