
<?php


#  Copyright (C) 2004  ken restivo <ken@restivo.org>
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



require_once "HTML/QuickForm.php";
require_once "HTML/QuickForm/group.php";
require_once('CoopObject.php');
require_once('HTML/QuickForm.php');
require_once('HTML/QuickForm/static.php');


class paypalForm
{
	var $title;
	var $account = 'beecooke@yahoo.com';
	var $server = 'https://www.paypal.com/cgi-bin/webscr';
    var $notify_url ="http://www.pacificacoop.org/sf/ipn.php";


	// THIS IS BORKEN
	function buildOldPayPalForm($title,  $formname,  $headerflag = 1)
		{
			$this->title = $title;

            $parth = pathinfo($_SERVER['SCRIPT_FILENAME']);
            $dir = $parth['dirname'] ;
            if(preg_match('/-dev/', $dir) > 0 ) {
                $this->account = "billing@restivo.org"  ;
				$this->server = 
					'https://www.sandbox.paypal.com/cgi-bin/webscr';
                $urlsuffix = "-dev";
            }
			
			$form =& HTML_QuickForm($formname, 'get', 
									$this->server, false, 1);
			if($headerflag){
				$this->addElement('header', 'tickets', $title);
			}	
			$this->addElement('hidden', 'cmd', '_xclick');
			$this->addElement('hidden', 'business', $this->account);
			$this->addElement('hidden', 'item_name', $title);
			$this->addElement("hidden", "item_number", 
							  $_REQUEST['source'] ? $_REQUEST['source'] : "EmailBlast");
			$this->addElement("hidden", "quantity", "1");
			$this->addElement("hidden", "page_style", "Primary");
			$this->addElement("hidden", "notify_url", 
							  "http://www.pacificacoop.org/sf$urlsuffix/ipn.php");
			$this->addElement("hidden", "return", 
							  "http://www.pacificacoop.org/sf$urlsuffix/thankyou.php");
			$this->addElement("hidden", "cancel", 
							  "http://www.pacificacoop.org/sf$urlsuffix/donate.php");
			$this->addElement("hidden", "no_note", "1");
			$this->addElement("hidden", "currency_code", "USD");
		}


	// THIS IS OLDE AND SHITTY
	function buildSelect($fieldname, $prices_raw, $select_first = 1,
						 $choose_one = 0)
		{

			if($choose_one){
				$prices[] = "-- Choose One--"; 
			}
			foreach($prices_raw as $descr => $price){
				$prices[$price] = sprintf($descr, $price) ;
			}
			$sel =& HTML_QuickForm::createElement(
				"select", $fieldname, "Select one:", $prices, 
				array('size' => count($prices)));
			if ($select_first){
				$sel->setSelected(array_shift(array_keys($prices)));
			}
			return $sel;
		}
    

	// the new improved form
	function buildRSVP(&$cp)
		{
			
			$form =& new HTML_QuickForm( 'Springfest RSVP', 'rsvpform');
			
			// ticket quantity box NOTE: use "invoice" when sumbitting to paypal
			$tick[] =& HTML_QuickForm::createElement('text', 
												   'ticket_quantity', 
												   'Yes! Please reserve', 
												   'size="4"');
			$tick[] =& new HTML_QuickForm_static('moretext', false,
											'<b>tickets at $25 per person</b>');
			//confessArray($tick, 'tick');
			$form->addGroup($tick, false, 'Yes! Please reserve', '&nbsp;');
			
			// dynamically add OTHER box based on its presence
			$form->addElement('text', 'other_amount', 
							  'I/we will be unable to attend, 
				but would like to make a tax-deductible contribution of: ',
							  'size="4"');
			
			// a frozen TOTAL DONATION box too, before they paypal in
			$form->addElement('submit', 'verify', 'Next>>');
			
			$form->setDefaults(array('other_amount' => '$'));

			// important
			if(SID){
				$form->addElement('hidden', 'coop', session_id()); 
			}
			
			//TODO pass through ANY OTHER VARS!
			// i.e. the lead id, weirdo paypal.php vars, etc
			$form->addElement('hidden', 'response_code', 
							  $_REQUEST['response_code']); 

			$form->applyFilter('__ALL__', 'trim');
			
			return $form;
			
		} // END BUILDRSVP
	
} // end paypalform class

// keep below
?>
<!-- PAYPAL -->




