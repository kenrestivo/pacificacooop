<?php 

//$Id$

/*
	Copyright (C) 2004-2006  ken restivo <ken@restivo.org>
	 
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


//////////////////////////////////////////
/////////////////////// THANKYOU CLASS
class ThankYou
{
	var $cp ;  // alias to coop page object
	/// list of fields:
	var $date ; // DATE: date of letter
	var $name; // NAME: address of who it gets sent to
	var $dear; // the DEAR: name
	var $address_array; // ADDRESS: multiple line array, the address
	var $items_array; // ITEMS: multiple line array, list of things they donated
	var $value_received_array; 	// value of things received
	var $iteration; // ITERATION: the number of springfests so far.
					// system calculates this for you.
	var $ordinal; // ORDINAL: st/nd/rd, etc. system calculates this.
	var $year; // YEAR: the year of this springfest. system calclates this
	var $years; // YEARS: how many years since the school was founded
	var $from; // FROM: who sent the letter. will get filled in with
			   // name of solicitor
	var $email; // EMAIL: email address
	var $thank_you_id; // cache of the unique id for this thankyounote
	var $entityHack; // cache of array of entity and id
	var $check_reconcile;  // stupid flag to avoid checking reconciliation
	var $template = array(); // get that this out of the db



	function ThankYou(&$cp)
		{
			if(!is_object($cp)){
				user_error("must pass cooppage object in to thankyou", E_USER_ERROR);
			}
			$this->cp = $cp;
		}


    function fetchTemplate()
        {
            $t = new CoopObject(&$this->cp, 
                                 'thank_you_templates', &$nothing);
            $t->obj->whereAdd(sprintf('school_year = "%s"', findSchoolYear()));
            $t->obj->find(true);
            $this->template = $t->obj->toArray();
        }


	// a factory method
	function substitute()
		{
		
			// XXX broken. this needs to use chosenschoolyear instead
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
			$subst['ADDRESS'] = implode('<br />', $this->address_array);
			$subst['ITEMS'] = $this->formatArray($this->items_array);
			$subst['FROM'] = sprintf('<br /><br /><br />%s', $this->from);


	  			//confessObj($this, 'this');
			foreach(array_keys($subst) as $key){
				$from[] = sprintf('[:%s:]', $key);
			}
			$to = array_values($subst);

			return str_replace($from, $to, 
                                          $this->template['main_body']);

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

		
			$text .= str_replace($from, $to, $this->template['main_body']);
		
			
			//TODO: hack for the "tagline" at the bottom. preg_match?
			// if it starts with a " and ends with a ", curly and bolditalic
		
			return $text;
		}


    function formatArray($res)
        {
            // for web formatting, maybe in already-sent letters?
            //return implode('<br>', $res);

            // for thankyouletter
            if(count($res) > 1){
                $res[count($res) - 1 ] = 'and ' . $res[count($res) - 1];
            }

            return implode(count($res) > 2 ? ', ': ' ', $res);
        }



	// this renderd EMAIL/TXT format by default! override these to do html
	function varsToArray()
		{
			$subst['DATE']  = $this->date ; 
			$subst['DEAR'] =  $this->dear; 
			$subst['NAME'] =  $this->name; 
			$subst['ITERATION'] = $this->iteration; 
			$subst['ORDINAL'] = $this->ordinal; 
			$subst['YEAR'] =  $this->year; 
			$subst['YEARS'] = $this->years; 
			$subst['FROM'] = $this->from;
			$subst['EMAIL'] = $this->email; 
			// i use the text default for these, html will override them anyway
			$subst['ADDRESS'] = implode("\n", $this->address_array);
			$subst['ITEMS'] = $this->formatArray($this->items_array);
			if(count($this->value_received_array)){
				$subst['VALUERECEIVED'] = $this->template['value_received'];
				$subst['VALUERECEIVED'] .= $this->formatArray($this->value_received_array);
			} else {
				$subst['VALUERECEIVED'] = $this->template['no_value'];
			}

			/// XXX DEAR IN THE HEADLIGHTS HACK!
			/// i hate this so much, i removed it from the codebase
//			user_error(sprintf("[%s] is name", $this->name), E_USER_NOTICE);
// 			if(!trim($this->name)){
// 				user_error("$this->name is not name. yanking dear", 
// 						   E_USER_NOTICE);
//  				//yank the line beginning with 'Dear'
// 				// remove it from the template!
// 				$this->template = preg_replace('/\nDear.+?\n/', "\n\n", 
// 											   $this->template);
// 			}

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
			if($this->cp->devSite()){
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
            $this->fetchTemplate();

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
            $cashtotal = 0;

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
				$this->name = implode(' ', array($co->obj->salutation,
                                                 $co->obj->first_name, 
                                                 $co->obj->last_name));
                $this->dear = implode(' ', 
                                      array($co->obj->salutation? 
                                            $co->obj->salutation : 
                                            $co->obj->first_name,
                                            $co->obj->last_name));
			}

			foreach(array('company_name', 'address1', 'address2') as $var){
				if($co->obj->$var){
					$this->address_array[] = $co->obj->$var;
				}
			}
			$this->address_array[] = sprintf("%s, %s %s", 
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
				$this->items_array[] = sprintf(
					'$%01.02f %s', $cashtotal, $this->template['cash']);
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
				$this->items_array[] = $real->obj->short_description;
										
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
				$this->items_array[] = $real->obj->item_description;
										
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

			//TODO: check that value < cashrecived, or auction/in-kind?
			$value = $this->getValueReceived($pk, $id, $sy);

			
			return true;
		}
	
	  function _findLeadThanksNeeded($pk, $id, $save)
		{

			// clear out some items in case i re-use objects
			$this->address_array = array(); 
			$this->items_array = array(); 
            $cashtotal = 0;
            $found = 0;

			// if i'm going to save objects, don't createlegacy them.
			// save MY view objects, not the DBDO objects,
			// so that i can createlegacy them later if needed.
			
			// XXX this is crazy. doing findschoolyear on EVERY one?
			// make it a damned arg
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
				$this->name = implode(' ', array(
									  $co->obj->salutation, 
									  $co->obj->first_name, 
									  $co->obj->last_name));
                $this->dear = implode(' ', 
                                      array($co->obj->salutation? 
                                            $co->obj->salutation : 
                                            $co->obj->first_name,
                                            $co->obj->last_name));

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
                if(isset($real->obj->family_id)){
                    $soliciting_families[]= $real->obj->family_id;
                }
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
			$real->obj->whereAdd(
				sprintf(
					'(thank_you_id is null or thank_you_id < 1) %s',
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
				$this->items_array[] = sprintf(
					'$%01.02f %s', $cashtotal, $this->template['cash']);
			}
					

			if(!count($this->items_array)){
				return false;
			}

			$value = $this->getValueReceived($pk, $id, $sy);
			
			// ok, whack the ticket-only people
			if($value >= $cashtotal){
				$this->items_array = array(); // XXX HACK!
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
			return $co->obj->{$co->pk};
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
			return $co->obj->{$co->pk};
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
				}
			}
			if($found){
				$this->items_array[] = sprintf(
                    "$%01.02f %s", 
                    $cashtotal, $this->template['cash']);
			}
				

			//AUCTION
			$real = new CoopView(&$this->cp, 'auction_donation_items', 
								 &$co);
			$real->obj->thank_you_id = $this->thank_you_id;
			$save =  $real->obj; // need to cache it b4 we search
			$found = $real->obj->find();
			while($real->obj->fetch()){
				$this->items_array[] = $real->obj->short_description;
										
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
				$this->items_array[] = $real->obj->item_description;
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
			if($id = $this->guessCompany($company_guess_hack)){
				// ugh. go get the soliciting parent
				$this->guessParents($soliciting_families);
				$pk = 'company_id';
			} else if($id = $this->guessLead($lead_guess_hack)){
				//$this->fromFamily();
				$pk = 'lead_id';
			}

			$this->getValueReceived($pk, $id);

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
		
	  // could check N here too, alternately
	  if($mistake_summary){
		  $mistake_summary .= print_r($_REQUEST, true);
		  
		  // now send it
		  $this->cp->mailError("Item found with thank_you_id that points to no actual thankyou.", $mistake_summary);
	  }
	  
		} // END REPAIRORPHANS	


	function getValueReceived($pk, $id, $sy = NULL)
		{
            $valuereceived = "";
            $found = 0;

			$sy || $sy = findSchoolYear();
			$this->value_received_array = array(); // clear it! save!

			//VALUE RECEIVED
			//find ads
			$co = new CoopObject(&$this->cp, 'ads', 
								 &$top);
			$co->obj->$pk = $id;
			$co->obj->school_year = $sy;
			$real = new CoopView(&$this->cp, 'ad_sizes', 
								 &$co);
			$real->obj->joinadd($co->obj);
			$real->obj->find();

			while($real->obj->fetch()){
				$this->value_received_array[] = sprintf(
					"one %s %s $%01.02f",
					$real->obj->ad_size_description,
                    $this->template['ad'],
					$real->obj->ad_price);
			}


			//find tickets
			$co = new CoopObject(&$this->cp, 'tickets', 
								 &$top);
			$co->obj->$pk = $id;
			$co->obj->school_year = $sy;
			$co->obj->find();

			while($co->obj->fetch()){
				$pad = new CoopObject(&$this->cp, 'springfest_attendees',
									  &$top);

				// just the tip
				$pad->obj->{$co->pk} = $co->obj->{$co->pk};
				$pad->obj->school_year = $sy;
				$pad->obj->attended = 'Yes'; 
				$found += $pad->obj->find();

			}
			// afterwards, in case they did multiple ticket purchases
			if($found){
				$valuereceived = $found * COOP_SPRINGFEST_TICKET_PRICE; // XXX 
				$this->value_received_array[] = sprintf(
					"%s ticket%s %s $%01.02f",
					$found, 
                    $found > 1 ? 's': '',
                    $this->template['ticket'],
                    $valuereceived);

			}
			return $valuereceived;

		} // end getvaluereceived()


} // END THANK YOU CLASS


////KEEP EVERTHANG BELOW

?>
