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
require_once('Mail.php');
require_once('object-config.php');
require_once('DB/DataObject/Cast.php');

define('COOP_FOUNDED', 1962);
define('FIRST_SPRINGFEST', 1972);


//////////////////////////////////////////
/////////////////////// THANKYOU CLASS
class ThankYou
{
	var $cp ;  // alias to coop page object
	/// list of fields:
	var $date ; // DATE: date of letter
	var $name; // NAME: address of who it gets sent to
	var $address_array; // ADDRESS: multiple line array, the address
	var $items_array; // ITEMS: multiple line array, list of things they donated
	var $iteration; // ITERATION: the number of springfests so far.
					// system calculates this for you.
	var $ordinal; // ORDINAL: st/nd/rd, etc. system calculates this.
	var $year; // YEAR: the year of this springfest. system calclates this
	var $years; // YEARS: how many springfests so far
	var $from; // FROM: who sent the letter. will get filled in with
			   // name of solicitor
	var $email; // EMAIL: email address
	var $thank_you_id; // cache of the unique id for this thankyounote
	var $entityHack; // cache of array of entity and id
	var $check_reconcile;  // stupid flag to avoid checking reconciliation

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

	function ThankYou(&$cp)
		{
			if(!is_object($cp)){
				user_error("must pass cooppage object in to thankyou", E_USER_ERROR);
			}
			$this->cp = $cp;
		}

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

			if(!count($this->items_array)){
				// TODO: guess if they are in a no-javascript situation
				// don't tell 'em to close their window if so!
				return "<p>Letter has already been saved. Close this window, then click on View/Edit to reprint it.</p>";
			}

			$subst = $this->varsToArray();

			//un-arrayify the ones that are arrays
			// and format them html-like
			//confessArray($this->address_array, 'addr');
			$subst['ADDRESS'] = implode('<br>', $this->address_array);
			$subst['ITEMS'] = implode(count($this->items_array) > 2 ?
											', ' : " and ", 
									  $this->items_array);
			$subst['FROM'] = sprintf('<br><br><br>%s', $this->from);
			$subst['ORDINAL'] = sprintf('<sup>%s</sup>', $this->ordinal);


	  			//confessObj($this, 'this');
			foreach(array_keys($subst) as $key){
				$from[] = sprintf('[:%s:]', $key);
			}
			$to = array_values($subst);

			$text .= '<div id="toplogo"><img src="/round-small-logo.gif" border="0" alt="Logo"></div>';
	
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

	// does it in EMAIL/TXT format by default! override these to do html
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
			$subst['ITEMS'] = implode(count($this->items_array) > 2 ? 
											', ' : ' and ', 
											$this->items_array);

			/// DEAR IN THE HEADLIGHTS HACK!
			user_error(sprintf("[%s] is name", $this->name), E_USER_NOTICE);
			if(!trim($this->name)){
				user_error("$this->name is not name. yanking dear", 
						   E_USER_NOTICE);
 				//yank the line beginning with 'Dear'
				// remove it from the template!
				$this->template = preg_replace('/\nDear.+?\n/', "\n\n", 
											   $this->template);
			}

			return $subst;
		}

	function sendEmail()
		{

			//print "EMAIL " . $this->email;
			
			//sanity czech
			if(!$this->email){
				PEAR::raiseError('no email address? huh?', 888);
								
				user_error(
					"thankyou::sendEmail(): no email address for $this->name!",
						   E_USER_ERROR);
			}
			
			// CHECK DEV SITE AND DO NOT SEND IT IF I'M ON THE DEV SITE!
			if(devSite()){
				printf("YOU ARE DEV. email is <pre>%s</pre>. goodbye now.",
					   $this->toText());
				return;
			}
			return;

			$from = $this->from ? $this->from :
					    'Pacifica Co-Op Nursery School ';
			// TODO: grab the parent's email

			$headers['From']    = sprintf('%s <thank_you@pacificacoop.org>',
										  $from);
			$headers['To']      = sprintf("%s <%s>", 
										  trim($this->name), 
										  trim($this->email));
			$headers['Subject'] = 'Thank you for your donation!';


			$mail_object =& Mail::factory('smtp', $params);

			$body = $this->toText();
			$mail_object->send($this->email, 
							   $headers, 
							   $body);

			return sprintf('<p>EMAIL SENT!</p><pre>%s</pre>',$body);
						   
		}

	// grab the REQUEST values from paypal, and put them in here
	// DOES THIS BELONG HERE!!?? or does it belong in parsepaypal?
	// it sucks being an OOP idiot.
	function parsePaypal($datefield = 'confirm_date')
		{
				//calculate all the crap we will need
			$this->items_array = array(sprintf("$%.2f via credit card", 
											   $_REQUEST['payment_gross']));
			$this->name = sprintf("%s %s", $_REQUEST['first_name'], 
								  $_REQUEST['last_name']); 
			$this->address_array = array($_REQUEST['address_street'], 
									   sprintf("%s, %s %s", 
											   $_REQUEST['address_city'],
											   $_REQUEST['address_state'],
											   $_REQUEST['address_zip']),
									   $_REQUEST['address_country']);
			$this->email = $_REQUEST['payer_email'];
			
			if($datefield == 'confirm_date'){
				preg_match('/^(\d{4})(\d{2})(\d{2}).*/', 
						   $_REQUEST['confirm_date'], $matches);
				$this->date = date('l, F j, Y',
								   mktime(0,0,0,
										  $matches[2], $matches[3], $matches[1]));
			} else {
				$this->date = $_REQUEST[$datefield];
			}
				//print_r($matches);
	
		}

	// XXX ugly hack. i'm passing in the type of save, as the arg $save
	// which really should just be a true/false flag. bah.
	// the right thing to do is to separate finding, substituting, and saving
			// returns true when it saves and/or finds
	  function findThanksNeeded($pk, $id, $save = false)
		{
			switch($pk){
			case 'lead_id':
				return $this->_findLeadThanksNeeded($pk, $id, $save);
				break;
			case 'company_id':
				return $this->_findSolicitThanksNeeded($pk, $id, $save);
				break;
			default:
				PEAR::raiseError('no pk provided!', 999);
				break;
			}

		}
	
	  function _findSolicitThanksNeeded($pk, $id, $save)
		{

			// clear out some items in case i re-use objects
			$this->address_array = array(); 
			$this->items_array = array(); 

			// if i'm going to save objects, don't createlegacy them.
			// save MY view objects, not the DBDO objects,
			// so that i can createlegacy them later if needed.
			
			$sy = findSchoolYear();
			
			// XXX BUG! save assumes there really *are* thankyous needed
			// do NOT use this anywhere that you're not sure of that.
				/// the trick is to call findThanksNeeded *twice*
				/// once with $save = false, and again to actually save.

				if($save){
					
					// first make sure i actually have stuff to save first!
					$safety = new ThankYou(&$this->cp);
					$safety->findThanksNeeded($pk, $id, false);
					if(count($safety->items_array) < 1){
						PEAR::raiseError('Asked to save thankyous for this company, but none actually exist!', 666); 
						//this find is out of sync with the one in show
						return false;
					}

					// save a new thankyou, and cache ists insertid	
					$co = new CoopObject(&$this->cp, 
										 'thank_you', &$nothing);
					$co->obj->date_sent = date('Y-m-d');
					$co->obj->family_id = $this->cp->userStruct['family_id'];
					$co->obj->method = $save; /// HACK!
					$co->obj->insert();
					$this->thank_you_id = $co->lastInsertID();
					// do audit AFTER last insertid above!
					$co->saveAudit(true);
				}
				
				// COMPANY
				// find company
			$co = new CoopView(&$this->cp, 'companies', &$top);
			$co->obj->$pk = $id;
			$co->obj->find(true);

			// format company
			if($co->obj->first_name || $co->obj->last_name){
				$this->name = sprintf('%s %s', $co->obj->first_name, 
									  $co->obj->last_name);
			}

			foreach(array('company_name', 'address1', 'address2') as $var){
				if($co->obj->$var){
					$this->address_array[] = $co->obj->$var;
				}
			}
			$this->address_array[] = sprintf("%s %s, %s", 
											 $co->obj->city,
											 $co->obj->state,
											 $co->obj->zip);		
			$this->email = $co->obj->email_address;

			//confessArray($this->address_array, 'addrarray- co');
			//INCOME
			//find income
			$co = new CoopObject(&$this->cp, 'companies_income_join', &$top);
			$co->obj->$pk = $id;
			$real = new CoopView(&$this->cp, 'income', &$co);
			$real->obj->school_year = $sy;
			$real->obj->orderBy('school_year desc');
			$real->obj->joinadd($co->obj);
			$real->obj->whereAdd(sprintf('(thank_you_id is null or thank_you_id < 1) %s',
										 $this->check_reconcile ?  ' and cleared_date > "2000-01-01" ' : ' ' ));
			$found = $real->obj->find();
			
			//format income
			while($real->obj->fetch()){
				$cashtotal += $real->obj->payment_amount;
				$soliciting_families[]= $real->obj->family_id;
				if($save){
					$tmp = $real->obj;
					$real->obj->thank_you_id = $this->thank_you_id;
					$real->obj->update($tmp);
				}
			}
			if($found){
				$this->items_array[] = sprintf("$%01.02f cash", $cashtotal);
			}
				

			//AUCTION
			// find auction
			$co = new CoopObject(&$this->cp, 'companies_auction_join', 
								 &$top);
			$co->obj->$pk = $id;
			$real = new CoopView(&$this->cp, 'auction_donation_items', 
								 &$co);
			$real->obj->orderBy('school_year desc');
			$real->obj->school_year = $sy;
			$real->obj->whereAdd('(thank_you_id is null or thank_you_id < 1) 
						and date_received > "2000-01-01" ');
			$real->obj->joinadd($co->obj);
			$found = $real->obj->find();

			// format auction
			while($real->obj->fetch()){
				$this->items_array[] = sprintf("%s (total value $%01.02f)",
											   $real->obj->item_description,
											   $real->obj->item_value);
				$soliciting_families[]= $real->obj->family_id;
				if($save){
					$tmp = $real->obj;
					$real->obj->thank_you_id = $this->thank_you_id;
					$real->obj->update($tmp);
				}
				
			}

			//IN-KIND
			//find in-kind
			$co = new CoopObject(&$this->cp, 'companies_in_kind_join', 
								 &$top);
			$co->obj->$pk = $id;
			$real = new CoopView(&$this->cp, 'in_kind_donations', 
								 &$co);
			$real->obj->orderBy('school_year desc');
			$real->obj->school_year = $sy;
			$real->obj->whereAdd('(thank_you_id is null or thank_you_id < 1) ');
			// TODO add date received back in later
			$real->obj->joinadd($co->obj);
			$real->obj->find();

			//format in-kind
			while($real->obj->fetch()){
				$this->items_array[] = sprintf("%s total value $%01.02f",
											   $real->obj->item_description,
											   $real->obj->item_value);
				$soliciting_families[]= $real->obj->family_id;
				if($save){
					$tmp = $real->obj;
					$real->obj->thank_you_id = $this->thank_you_id;
					$real->obj->update($tmp);
				}
			}
	
			// ugh. go get the soliciting parent
			$this->guessParents($soliciting_families);

			if(!count($this->items_array)){
				return false;
			}
			
			return true;
		}
	
	  function _findLeadThanksNeeded($pk, $id, $save)
		{

			// clear out some items in case i re-use objects
			$this->address_array = array(); 
			$this->items_array = array(); 

			// if i'm going to save objects, don't createlegacy them.
			// save MY view objects, not the DBDO objects,
			// so that i can createlegacy them later if needed.
			
			$sy = findSchoolYear();
			
			// XXX BUG! save assumes there really *are* thankyous needed
			// do NOT use this anywhere that you're not sure of that.
				/// the trick is to call findThanksNeeded *twice*
				/// once with $save = false, and again to actually save.

				if($save){
					
					// first make sure i actually have stuff to save first!
					$safety = new ThankYou(&$this->cp);
					$safety->findThanksNeeded($pk, $id, false);
					if(count($safety->items_array) < 1){
						PEAR::raiseError('Asked to save thankyous for this lead, but none actually exist!', 666); 
						//this find is out of sync with the one in show
						return false;
					}

					// save a new thankyou, and cache ists insertid	
					$co = new CoopObject(&$this->cp, 
										 'thank_you', &$nothing);
					$co->obj->date_sent = date('Y-m-d');
					$co->obj->family_id = $this->cp->userStruct['family_id'];
					$co->obj->method = $save; /// HACK!
					$co->obj->insert();
					$this->thank_you_id = $co->lastInsertID();
					// do audit AFTER last insertid above!
					$co->saveAudit(true);
				}
				
				// LEAD
				// find lead
			$co = new CoopView(&$this->cp, 'leads', &$top);
			$co->obj->$pk = $id;
			$co->obj->find(true);

			// format lead
			if($co->obj->first_name || $co->obj->last_name){
				$this->name = sprintf('%s %s', $co->obj->first_name, 
									  $co->obj->last_name);
			}

			foreach(array('company', 'address1', 'address2') as $var){
				if($co->obj->$var){
					$this->address_array[] = $co->obj->$var;
				}
			}
			$this->address_array[] = sprintf("%s %s, %s", 
											 $co->obj->city,
											 $co->obj->state,
											 $co->obj->zip);		
			$this->email = $co->obj->email_address;

			//INCOME, from donations
			//find income
			$co = new CoopObject(&$this->cp, 'leads_income_join', &$top);
			$co->obj->$pk = $id;
			$real = new CoopView(&$this->cp, 'income', &$co);
			$real->obj->school_year = $sy;
			$real->obj->orderBy('school_year desc');
			$real->obj->joinadd($co->obj);
			$real->obj->whereAdd(sprintf('(thank_you_id is null or thank_you_id < 1) %s',
										 $this->check_reconcile ?  ' and cleared_date > "2000-01-01" ' : ' ' ));
			$found += $real->obj->find();
			
			//format income
			while($real->obj->fetch()){
				$cashtotal += $real->obj->payment_amount;
				$soliciting_families[]= $real->obj->family_id;
				if($save){
					$tmp = $real->obj;
					$real->obj->thank_you_id = $this->thank_you_id;
					$real->obj->update($tmp);
				}
			}

			//INCOME, from tickets
			//find income
			$co = new CoopObject(&$this->cp, 'tickets', &$top);
			$co->obj->$pk = $id;
			$real = new CoopView(&$this->cp, 'income', &$co);
			$real->obj->school_year = $sy;
			$real->obj->orderBy('income.school_year desc');
			$real->obj->joinadd($co->obj);
			$real->obj->whereAdd(sprintf('(thank_you_id is null or thank_you_id < 1) %s',
										 $this->check_reconcile ?  ' and cleared_date > "2000-01-01" ' : ' ' ));
			$found += $real->obj->find();
			
			//format income
			while($real->obj->fetch()){
				$cashtotal += $real->obj->payment_amount;
				$soliciting_families[]= $real->obj->family_id;
				if($save){
					$tmp = $real->obj;
					$real->obj->thank_you_id = $this->thank_you_id;
					$real->obj->update($tmp);
				}
			}


			if($found){
				$this->items_array[] = sprintf("$%01.02f cash", $cashtotal);
			}
					

			if(!count($this->items_array)){
				return false;
			}
			
			return true;
		}
	
	// takes array of familyid's, and fills in thisfrom with the parents
	function guessParents($family_id_array)
		{
			if(!array_sum($family_id_array)){
				return false;
			}
			foreach(array_unique($family_id_array) as $fam){
				$par =& new CoopObject(&$this->cp, 'parents', 
									   &$top);
				$par->obj->family_id = $fam;
				$par->obj->type = 'Mom';
				$par->obj->find(true);
				$solicit_parents[] = sprintf("%s %s", 
											 $par->obj->first_name, 
											 $par->obj->last_name);
			}
			$this->from = implode(", ", $solicit_parents);
			return true;
		}
	
	// only returns the FIRST company found
	// returns false if none found
	function guessCompany($company_id_array)
		{
			
			if(!array_sum($company_id_array)){
				return false;
			}
			foreach(array_unique($company_id_array) as $cid){
				$co =& new CoopObject(&$this->cp, 'companies', 
									   &$top);
				$co->obj->company_id = $cid;
				if($co->obj->find(true)){
					$this->entityHack = array('companies' => $cid);
					break;
				}
			}
			foreach(array('company_name', 'address1', 'address2') as $var){
				if($co->obj->$var){
					$this->address_array[] = $co->obj->$var;
				}
			}
			$this->address_array[] = sprintf("%s %s, %s", 
											 $co->obj->city,
											 $co->obj->state,
											 $co->obj->zip);		
			$this->name = sprintf('%s %s', $co->obj->first_name, 
								  $co->obj->last_name);
			return true;
		}

	// only returns the FIRST lead found
	// returns false if none found
	function guessLead($lead_id_array)
		{
			
			if(!array_sum($lead_id_array)){
				return false;
			}
			foreach(array_unique($lead_id_array) as $cid){
				$co =& new CoopObject(&$this->cp, 'leads', 
									   &$top);
				$co->obj->lead_id = $cid;
				if($co->obj->find(true)){
					$this->entityHack = array('leads' => $cid);
					break;
				}
			}
			//confessObj($co->obj, 'obj');

			foreach(array('company', 'address1', 'address2') as $var){
				if($co->obj->$var){
					$this->address_array[] = $co->obj->$var;
				}
			}
			$this->address_array[] = sprintf("%s %s, %s", 
											 $co->obj->city,
											 $co->obj->state,
											 $co->obj->zip);		
			$this->name = sprintf('%s %s', $co->obj->first_name, 
								  $co->obj->last_name);
			return true;
		}

	// populates a thank-you note with what's already in that note.
	// XXX hideous, nasty duplication of code. i need to abstract out
	// the display from the finding, and pass objects instead
	function recoverExisting($tid)
		{

			$this->thank_you_id = $tid;
			$company_guess_hack = array();
			$family_guess_hack = array();
			$lead_guess_hack = array();

			// TODO recover date of THANK YOU, not today's date
			$ty = new CoopObject(&$this->cp, 'thank_you', &$nothing);
			$ty->obj->thank_you_id = $this->thank_you_id;
			$ty->obj->selectAdd(
				"DATE_FORMAT(date_sent,'%W, %M %e, %Y') as date_sent_fmt ");

			$ty->obj->find(true);
			$this->date = $ty->obj->date_sent_fmt;

			//INCOME
			$real = new CoopView(&$this->cp, 'income', &$co);
			$real->obj->thank_you_id = $this->thank_you_id;
			$save =  $real->obj; // need to cache it b4 we search
			$found = $real->obj->find();
			while($real->obj->fetch()){
				$cashtotal += $real->obj->payment_amount;
				// OMG. this is so fucking ugly, i can't describe it
				$sf =& new CoopObject(&$this->cp , 
									  'companies_income_join', &$real);
				$sf->obj->joinAdd($save);
				$sf->obj->find(true);
				$soliciting_families[]= $sf->obj->family_id;
				$company_guess_hack[] =$sf->obj->company_id;

				//check leads_income *and* tickets
				$sf =& new CoopObject(&$this->cp , 
									  'leads_income_join', &$real);
				$sf->obj->joinAdd($save);
				$lijfound = $sf->obj->find(true);
				if($lijfound){
					$lead_guess_hack[]= $sf->obj->lead_id;
				}

				$sf =& new CoopObject(&$this->cp , 
									  'tickets', &$real);
				$sf->obj->joinAdd($save);
				$ticketfound = $sf->obj->find(true);
				if($ticketfound){
					$lead_guess_hack[]= $sf->obj->lead_id;
					$this->items_array[] = sprintf('%d tickets to Springfest',
											   $sf->obj->ticket_quantity);
				}
			}
			if($found){
				$this->items_array[] = sprintf("$%01.02f cash", $cashtotal);
			}
				

			//AUCTION
			$real = new CoopView(&$this->cp, 'auction_donation_items', 
								 &$co);
			$real->obj->thank_you_id = $this->thank_you_id;
			$save =  $real->obj; // need to cache it b4 we search
			$found = $real->obj->find();
			while($real->obj->fetch()){
				$this->items_array[] = sprintf("%s (total value $%01.02f)",
											   $real->obj->item_description,
											   $real->obj->item_value);
				$sf =& new CoopObject(&$this->cp , 
									  'companies_auction_join', &$real);
				$sf->obj->joinAdd($save);
				$sf->obj->find(true);
				$soliciting_families[]= $sf->obj->family_id;
				$company_guess_hack[] =$sf->obj->company_id;
			}

			//IN-KIND
			$real = new CoopView(&$this->cp, 'in_kind_donations', 
								 &$co);
			$real->obj->thank_you_id = $this->thank_you_id;
			$save =  $real->obj; // need to cache it b4 we search
			$real->obj->find();
			while($real->obj->fetch()){
				$this->items_array[] = sprintf("%s total value $%01.02f",
											   $real->obj->item_description,
											   $real->obj->item_value);
				$sf =& new CoopObject(&$this->cp , 
									  'companies_in_kind_join', &$real);
				$sf->obj->joinAdd($save);
				$sf->obj->find(true);
				$soliciting_families[]= $sf->obj->family_id;
				$company_guess_hack[] =$sf->obj->company_id;
			}

			if(!count($this->items_array)){
				PEAR::raiseError('EMPTY thank you note', 777);
								
				return false;
			}
		

			//  COMPANIES BROKEN! have to guess from income or inkind. bah. 
			if($this->guessCompany($company_guess_hack)){
				// ugh. go get the soliciting parent
				$this->guessParents($soliciting_families);
			} else if($this->guessLead($lead_guess_hack)){
				//$this->fromFamily();
			}

			return true;
		}

	function repairOrphaned()
		{
	
			foreach(array('in_kind_donations', 'auction_donation_items',
						  'income') as $table)
			{
				// have to save it b4 each query
				$save = $ty->obj;
				$real = new CoopView(&$this->cp, $table, &$nothing);
				$real->obj->query(sprintf("select %s.* from %s
						left join thank_you using (thank_you_id)
						where %s.thank_you_id > 0 
						and thank_you.thank_you_id is null", 
										  $table, $table, $table));
				//print $real->simpleTable(false);
				//continue;
				while($real->obj->fetch()){
					// silly superstitious hack. i don't trust dbdo anymore
					if($real->obj->thank_you_id < 1){
						continue;
					}
					$mistake_summary .= print_r($real->obj, true);
					//clear it now! or try at least...
					user_error(sprintf("repairOrphaned(): clearing %d from %s", 
									   $real->obj->thank_you_id, $table), 
							   E_USER_NOTICE);
					$old = $real->obj; //  hacks around DBDO bugs
					$new = $real->obj; //  hacks around DBDO bugs
					$new->thank_you_id = DB_DataObject_Cast::sql('NULL');
					if(!$new->update($old)){
						PEAR::raiseError("failed to update when cleaning up orphaned thank you's. this is really bad. you probably have a corrupt database. stop immediately.", 555);
					}
				}
			} // end foreach
					
	  $mistake_summary .= print_r($_REQUEST, true);
					
	  // now send it
	  $this->cp->mailError("Item found with thank_you_id that points to no actual thankyou.", $mistake_summary);

	  
		} // END REPAIRORPHANS	

} // END THANK YOU CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END THANKYOUCLASS -->


