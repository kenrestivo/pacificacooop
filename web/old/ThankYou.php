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
	/// list of fields:
	var $date ; // DATE: date of letter
	var $name; // NAME: address of who it gets sent to
	var $address_array; // ADDRESS: multiple line array, the address
	var $items_array; // ITEMS: multiple line array, list of things they donated
	var $iteration; // ITERATION: the number of springfests so far. system calculates this for you.
	var $ordinal; // ORDINAL: st/nd/rd, etc. system calculates this.
	var $year; // YEAR: the year of this springfest. system calclates this
	var $years; // YEARS: how many springfests so far
	var $from; // FROM: who sent the letter. will get filled in with name of solicitor
	var $email; // EMAIL: email address

	//TODO: put this in a file, and fopen it, or in a DB blob!
	// if i put in in a db, schoolyearify them, and grab this years or latest
	// so they can override it in the future
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

	// a factory method
	function substitute()
		{
		
			
			$sy = findSchoolYear();

			// set defaults if empty: date, schoolyear, etc
			if(!$this->year){
				$tmp = explode('-', $sy);
				$this->year = $tmp[1];
			}

			if(!$this->years){
				$this->years = $this->year - COOP_FOUNDED;
			}

			if(!$this->iteration){
				$this->iteration = $this->year - FIRST_SPRINGFEST;
			}
			if(!$this->ordinal){
				$this->ordinal = $this->makeOrdinal($this->iteration);
			}
			
			if(!$this->date){
				$this->date = date('l, F j, Y');
			}
			
			// leave from blank if it's not there
				if (!$this->from){
					$this->from = "";
				}

		}

	function toHTML()
		{

			$subst = $this->varsToArray();

			//un-arrayify the ones that are arrays
			// and format them html-like
			$subst['ADDRESS'] = implode('<br>', $this->address_array);
			$subst['ITEMS'] = implode(',', $this->items_array);
			$subst['FROM'] = sprintf('<br><br><br>%s', $this->from);
			$subst['ORDINAL'] = sprintf('<sup>%s</sup>', $this->ordinal);

			
	  			//confessObj($this, 'this');
			foreach(array_keys($subst) as $key){
				$from[] = sprintf('[:%s:]', $key);
			}
			$to = array_values($subst);

			$text .= '<div id="toplogo"><img src="/round-small-logo.gif"></div>';
	
			$text .= '<div id="mainletter"><p class="letter">';
			$text .= str_replace($from, $to, 
								 nl2br(htmlspecialchars($this->template)));
			$text .= '</p></div>';

			//hack for the "tagline" at the bottom. 
			// if it starts with a " and ends with a ",  bolditalic
			$text = preg_replace('/&quot;(.+?)&quot;/', 
								 '<strong><i>&quot;$1&quot;</i></strong>;', 
								 $text);
			
			return $text;
		}

	//returns just the ordinal part
	function makeOrdinal($number)
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

			$subst = $this->varsToArray();

			//un-arrayify the ones that are arrays
			// and format them html-like
			$subst['ADDRESS'] = implode("\n", $this->address_array);
			$subst['ITEMS'] = implode(',', $this->items_array);
			$subst['FROM'] = sprintf("\n\n\n%s", $this->from);
			$subst['ORDINAL'] = sprintf('%s', $this->ordinal);

			
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

	function varsToArray()
		{
			$subst['DATE']  = $this->date ; 
			$subst['NAME'] =  $this->name; 
			$subst['ITERATION'] = $this->iteration; 
			$subst['ORDINAL'] = $this->ordinal; 
			$subst['YEAR'] =  $this->year; 
			$subst['YEARS'] = $this->years; 
			$subst['FROM'] = $this->from;
			$subct['EMAIL'] = $this->email; 
			// i use the text default for these, html will override them anyway
			$subst['ADDRESS'] = implode("\n", $this->address_array);
			$subst['ITEMS'] = implode(',', $this->items_array);

			return $subst;
		}


} // END THANK YOU CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END THANKYOUCLASS -->


