<?php

/*
	<!-- $Id$ -->
	the vital setup stuff that ALL files MUST have

  Copyright (C) 2003  ken restivo <ken@restivo.org>
 
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


require_once("object-config.php");
require_once("utils.inc");

class PostPaypal
{
	var $family_id;
	var $leads_id;
	var $account_number;
	var $company_id;
	var $uid;

	function factoryWrapper($tablename) 
		{
			
			$obj =& DB_DataObject::factory ($tablename);
			if (PEAR::isError($obj)){
				die ($obj->getMessage ());
			}
//			print_r($this->obj);
			return $obj;
		}
	
	// parse out the custom field from paypal
	function parseCustom($custom)
		{
			// split out all :'s
			// save them into var's
		}

	// actually saves a paypal transaction as an income item
	function postTransaction($uid)
		{
			$this->uid = $uid;

		}

	function postIncome()
		{
			$paypalobj = $this->factoryWrapper('accounting_paypal');
			$incobj = $this->factoryWrapper('income');
			$paypalobj->get($this->uid);

			foreach (array('txn_id','check_number') as $key => $val){ 
				$incobj->$val = $paypalobj->txn_id;
			}
			foreach (array('bookkeeper_date','cleared_date') as $key => $val){ 
				$incobj->$val = $paypalobj->confirm_date;
			}
			$incobj->payer = sprintf("%s %s", $paypalobj->first_name, $paypalobj->last_name);

	}


} /// END PAYPAL CLASS



# end of inner php code

?>

<!-- END POSTPAYPAL CLASS -->
