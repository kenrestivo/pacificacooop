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
require_once("DB/DataObject.php");

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
			
			$obj = DB_DataObject::factory ($tablename);
			if (PEAR::isError($obj)){
				die ($obj->getMessage ());
			}
//			print_r($this->obj);
			return $obj;
		}
	
	// parse out the custom field from paypal
	function parseCustom()
		{
            $custom =  $this->paypal_obj->custom;
			// split out all :'s
			$pairs = explode(":", $custom);
			foreach ($pairs as $nothing => $pair){
				preg_match('/(\w+?)(\d+)/', $pair, $matches);
				$index = $matches[1];
				$value = $matches[2];
				$longname = $this->key_mapping[$index];
				// save them into var's
				$this->$longname = $value;
				//print "index $index longname $longname value $value\n";
			}
		}

	// actually saves a paypal transaction as an income item
	//this is the main engine
	function postTransaction($uid)
		{
			$this->uid = $uid;
			$this->paypal_obj =& $this->factoryWrapper('accounting_paypal');
			$this->paypal_obj->get($this->uid);
            
            $this->parseCustom();
			if($this->postIncome()){
                $this->postFamily(); // TODO: handle leads/companies too
            }
            print_r($this);
		}

	function postIncome()
		{
			$obj =& $this->factoryWrapper('income');

            $obj->txn_id = $this->paypal_obj->txn_id;

            //print_r($obj);
            $obj->debugLevel(5);
            //don't dupe. XXX this is dumb. what do i do about refunds?
            $obj->whereAdd(sprintf("txn_id = '%s'", $obj->txn_id));
			$numrows = $obj->find() ; 
            //print "NUM $numrows";
            if($numrows > 0){
                return 0;   
            }

			foreach (array('txn_id','check_number') as $key => $val){ 
				$obj->$val = $this->paypal_obj->txn_id;
			}
			foreach (array('bookkeeper_date','cleared_date') as $key => $val){ 
				$obj->$val = $this->paypal_obj->confirm_date;
			}
			$obj->payer = sprintf("%s %s", 
                                     $this->paypal_obj->first_name, 
                                     $this->paypal_obj->last_name);

            $obj->amount = $this->paypal_obj->payment_gross;
            $obj->school_year = findSchoolYear();
			$obj->account_number = $this->account_number;
            //print_r($this);
            $this->income_id = $obj->insert();
            return 1;
	}

	function postFamily()
		{
		
			$obj =& $this->factoryWrapper('families_income_join');

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
