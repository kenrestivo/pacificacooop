
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



class paypalForm extends HTML_QuickForm
{
	var $title;


	function paypalForm($title,  $formname, $headerflag = 1)
		{
			$this->title = $title;
			
			$this->HTML_QuickForm($formname, 'get', 
								  'https://www.paypal.com/cgi-bin/webscr');
			if($headerflag){
				$this->addElement('header', 'tickets', $title);
			}	
			$this->addElement('hidden', 'cmd', '_xclick');
			$this->addElement('hidden', 'business', 'beecooke@yahoo.com');
			$this->addElement('hidden', 'item_name', $title);
			$this->addElement("hidden", "item_number", "EmailBlast");
			$this->addElement("hidden", "quantity", "1");
			$this->addElement("hidden", "page_style", "Primary");
			$this->addElement("hidden", "return", 
							  "http://www.pacificacoop.org/sf/thankyou.php");
			$this->addElement("hidden", "cancel", 
							  "http://www.pacificacoop.org/sf/donate.php");
			$this->addElement("hidden", "no_note", "1");
			$this->addElement("hidden", "currency_code", "USD");
		}



	function buildSelect($fieldname, $prices_raw, $select_first = 1,
						 $choose_one = 0)
		{

			if($choose_one){
				$prices[] = "-- Choose One--"; 
			}
			foreach($prices_raw as $descr => $price){
\				$prices[$price] = sprintf($descr, $price) ;
			}
			$sel =& HTML_QuickForm::createElement(
				"select", $fieldname, "Select one:", $prices, 
				array('size' => count($prices)));
			if ($select_first){
				$sel->setSelected(array_shift(array_keys($prices)));
			}
			return $sel;
		}
    
} // end paypalform class


// keep below
?>
<!-- PAYPAL -->




