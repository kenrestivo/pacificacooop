<?php

/*
	<!-- $Id$ -->

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


require_once("object-config.php");
require_once("utils.inc");
require_once("DB/DataObject.php");

class PostPaypal
{
	var $family_id;
	var $lead_id;
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
            
            // duck out if no custom. 
            ///XXX this will create orphaned cash tho! fix this dammit
            if(!$this->paypal_obj->custom){
                return;
            }
            
            // ok let's go now
            $this->parseCustom();

			if($this->postIncome()){
				// i hate this dispatching code.
                if($this->family_id){ 
					$this->postFamily(); 
					return;		// no thankyous for family
				}
                if($this->lead_id){
					$this->postLead(); 
				}
				$this->postThankYou();
            }
            //print_r($this);
		}

    function lastInsertID($obj)
        {
            $db =& $obj->getDatabaseConnection();

            $data =& $db->getOne('select last_insert_id()');
            if (DB::isError($data)) {
                die($data->getMessage());
            }
            return $data;
        }

	function postIncome()
		{
			$obj =& $this->factoryWrapper('income');

            $obj->txn_id = $this->paypal_obj->txn_id;

            //print_r($obj);
            //$obj->debugLevel(2);
            //don't dupe. XXX this is dumb. what do i do about refunds?
			$numrows = $obj->find() ; 
            //print "NUM $numrows";
            if($numrows > 0){
                return 0;   
            }

			if($this->paypal_obj->txn_id == '0'){
				//confessObj($this, 'this has no txn_id?');
				return 0;
			}

			foreach (array('txn_id','check_number') as $key => $val){ 
				$obj->$val = $this->paypal_obj->txn_id;
			}
			foreach (array('bookkeeper_date','cleared_date', 'check_date') 
                     as $key => $val){ 
				$obj->$val = $this->paypal_obj->confirm_date;
			}
			$obj->payer = sprintf("%s %s", 
                                     $this->paypal_obj->first_name, 
                                     $this->paypal_obj->last_name);

            $obj->payment_amount = $this->paypal_obj->payment_gross;
            $obj->school_year = findSchoolYear();
			$obj->account_number = $this->account_number;
            //print_r($this);
           
            $obj->insert();
            $this->income_id = $this->lastInsertID(&$obj); 
            return $this->income_id; // just in case
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
            $obj->insert(); // save the giblets
	}

	function postLead()
		{
			
			$table = $this->paypal_obj->invoice > 0 ? 'tickets' : 'leads_income_join';
				
			$obj =& $this->factoryWrapper($table);

            //don't dupe. XXX this is dumb. what do i do about refunds?
            $obj->income_id = $this->income_id;
            if($obj->find()){
                return;   
            }

			// here come da hokey hackies
			if($table == 'tickets'){
				$obj->ticket_type = 'Paid for';
				$obj->school_year = findSchoolYear();
				$obj->ticket_quantity = $this->paypal_obj->invoice;
			} 
			
		

            $obj->lead_id = $this->lead_id;
            $obj->insert(); // save the giblets
	}

	function postThankYou()
		{
		
			
			$ty =& $this->factoryWrapper('thank_you');
			//$ty->debugLevel(2);
			$ty->date_sent = date('Y-m-d');
			$ty->method = 'WebPage';
			$ty->insert(); // save the giblets
			$tyid = $this->lastInsertID(&$ty);
			

			// link it in. *sigh* tedious.
			$inc =& $this->factoryWrapper('income');
            $inc->income_id = $this->income_id;
            if(!$inc->find(true)){
                user_error("the income was saved, but i didn't find it. Bad.",
						   E_USER_ERROR);
            }
			$old = $inc;
			$inc->thank_you_id = $tyid;
			$inc->update($old);
			    
	}

} /// END POSTPAYPAL CLASS



# end of inner php code

?>

<!-- END POSTPAYPAL CLASS -->
