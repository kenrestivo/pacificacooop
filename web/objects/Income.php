<?php
/**
 * Table Definition for income
 */
require_once 'DB/DataObject.php';

class Income extends CoopDBDO 
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
									  'check_date',
                                      'account_number');
    var $fb_shortHeader = 'Income';

	var $fb_fieldLabels = array( 
		"check_number" => "Check or Credit Card Auth number" ,
		"check_date" => "Date of Check" ,
		"payer" => "Person issuing check" ,
		'payment_amount' => 'Amount ($)' ,
		"account_number" => "Account",
		"note" => "Misc Notes" ,
		'bookkeeper_date' => 'Date Given to Bookkeeper',
		'cleared_date' => 'Date Transaction Cleared the Bank',
		"school_year" => "School Year" ,
		'txn_id' => 'PayPal Credit Card Transaction ID',
		"thank_you_id" => "Thank-You Sent" 
		);

    // txn_id is a not-null field. set 0 as default
    var $fb_defaults = array('txn_id' => 0);
	var $fb_formHeaderText = "Cash Donations and Fees";

	var $fb_requiredFields = array('check_number', 'check_date', 'payer', 
								   'payment_amount', 'account_number', 
								   'school_year' );

    var $fb_currencyFields = array(
        'payment_amount'
        );


var $fb_dupeIgnore = array(
   'note'
);

   var $fb_sizes = array(
     'check_number' => 10
   );

    var $fb_joinPaths = array('family_id' => array('companies_income_join',
                                                   'families_income_join'));


// set check_number size = 10

// set account_number check_jointo = families

    function fb_linkConstraints(&$co)
        {

            ///XXX HIDEOUS HACK!! 

            $companies =& new CoopObject(&$co->page, 
                                         'companies_income_join', &$co);
            $co->protectedJoin(&$companies);


            $fam =& new CoopObject(&$co->page, 
                                         'families_income_join', &$co);
            $co->protectedJoin(&$fam);

            $co->constrainFamily();
            $co->constrainSchoolYear();
            $co->orderByLinkDisplay();
            $co->grouper();
            
        }


}
