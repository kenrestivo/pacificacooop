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
    var $income_id;
	var $uid;
	var $paypal_obj;
	var $key_mapping = array( 'fid' => 'family_id',
							  'coa' => 'account_number',
							  'lid' => 'lead_id',
							  'cid' => 'company_id');

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
			$pairs = explode(":", $custom);
			foreach ($pair as $nothing =>$pair){
				preg_match('/(\w+)(\d+)/', $pair, $matches);
				$index = $matches[1];
				$value = $matches[2];
				$longname = $this->key_mapping[$index];
				// save them into var's
				$this->$longname = $value;
			}
		}

	// actually saves a paypal transaction as an income item
	//this is the main engine
	function postTransaction($uid)
		{
			$this->uid = $uid;
			$this->paypal_obj->get($this->uid);
			$this->parseCustom();
			$this->postIncome();
            $this->postFamily(); // TODO: handle leads/companies too
		}

	function postIncome()
		{
			$this->paypal_obj = $this->factoryWrapper('accounting_paypal');
			$incobj = $this->factoryWrapper('income');

            //don't dupe. XXX this is dumb. what do i do about refunds?
            $incobj->txn_id = $this->paypal_obj->txn_id;
            if($incobj->find()){
                return;   
            }

			foreach (array('txn_id','check_number') as $key => $val){ 
				$incobj->$val = $this->paypal_obj->txn_id;
			}
			foreach (array('bookkeeper_date','cleared_date') as $key => $val){ 
				$incobj->$val = $this->paypal_obj->confirm_date;
			}
			$incobj->payer = sprintf("%s %s", 
                                     $this->paypal_obj->first_name, 
                                     $this->paypal_obj->last_name);

            $incobj->amount = $this->paypal_obj->payment_gross;
            $incobj->school_year = findSchoolYear();
			$incobj->account_number = $this->account_number;
			$this->income_id = $incobj->insert();
	}

	function postFamily()
		{
		
			$obj = $this->factoryWrapper('families_income_join');

            //don't dupe. XXX this is dumb. what do i do about refunds?
            $obj->income_id = $this->income_id;
            if($obj->find()){
                return;   
            }

            $obj->family_id = $this->family_id;
            $obj->insert(); // save the giblets?
	}


} /// END POSTPAYPAL CLASS



# end of inner php code

?>

<!-- END POSTPAYPAL CLASS -->
