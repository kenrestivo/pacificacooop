<?php
/**
 * Table Definition for income
 */
require_once 'DB/DataObject.php';

class Income extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'income';                          // table name
    var $income_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $check_number;                    // string(255)  
    var $check_date;                      // date(10)  binary
    var $payer;                           // string(255)  
    var $account_number;                  // int(32)  
    var $payment_amount;                  // real(11)  
    var $note;                            // string(255)  
    var $bookkeeper_date;                 // date(10)  binary
    var $cleared_date;                    // date(10)  binary
    var $school_year;                     // string(50)  
    var $txn_id;                          // string(20)  not_null
    var $thank_you_id;                    // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Income',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	
	//should work, but is ignored?
	var $fb_dateFields = array ('bookkeeper_date', 'cleared_date', 
								'check_date');
	var $fb_linkDisplayFields = array('payer', 
									  'payment_amount', 
									  'check_date');
	var $fb_fieldsToRender= array("check_date", "payer", 'school_year',
								  "account_number", "payment_amount", "note",
								  'thank_you_id');

	var $fb_fieldLabels = array( 
		"family_id" => "Co-Op Family",
		"check_number" => "Check or Credit Card Auth number" ,
		"check_date" => "Date of Check" ,
		"payer" => "Person issuing check" ,
		'payment_amount' => 'Amount ($)' ,
		"account_number" => "Account",
		"note" => "Misc Notes" ,
		"school_year" => "School Year" ,
		"thank_you_id" => "Thank-You Sent" 
		);
//	var $fb_crossLinks = array(array('table' => 'families_income_join', 
	//'fromFild' => 'income_id', 'toField' => 'family_id'));

	var $fb_formHeaderText = "Cash Donations and Fees";

}
