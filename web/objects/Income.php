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
    var $check_date;                      // date(10)  
    var $payer;                           // string(255)  
    var $account_number;                  // int(32)  
    var $payment_amount;                  // real(11)  
    var $note;                            // string(255)  
    var $bookkeeper_date;                 // date(10)  
    var $cleared_date;                    // date(10)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Income',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
