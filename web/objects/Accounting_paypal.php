<?php
/**
 * Table Definition for accounting_paypal
 */
require_once 'DB/DataObject.php';

class Accounting_paypal extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'accounting_paypal';               // table name
    var $uid;                             // int(20)  not_null primary_key auto_increment
    var $confirm_date;                    // timestamp(19)  not_null multiple_key unsigned zerofill binary timestamp
    var $item_name;                       // string(130)  not_null
    var $receiver_email;                  // string(125)  multiple_key
    var $item_number;                     // string(130)  not_null
    var $quantity;                        // int(6)  not_null
    var $invoice;                         // string(25)  not_null
    var $custom;                          // string(60)  
    var $payment_status;                  // string(31)  not_null multiple_key set
    var $pending_reason;                  // string(51)  not_null multiple_key set
    var $payment_gross;                   // real(12)  not_null
    var $payment_fee;                     // real(12)  not_null
    var $payment_type;                    // string(14)  not_null multiple_key set
    var $payment_date;                    // string(50)  not_null
    var $txn_id;                          // string(20)  not_null
    var $payer_id;                        // int(13)  
    var $payer_business_name;             // string(127)  
    var $payer_email;                     // string(125)  
    var $payer_status;                    // string(33)  not_null multiple_key set
    var $txn_type;                        // string(94)  not_null multiple_key set
    var $first_name;                      // string(35)  
    var $last_name;                       // string(60)  
    var $address_city;                    // string(60)  
    var $address_street;                  // string(60)  
    var $address_state;                   // string(60)  
    var $address_zip;                     // string(15)  
    var $address_country;                 // string(60)  
    var $address_status;                  // string(21)  not_null set
    var $subscr_date;                     // string(50)  not_null
    var $period1;                         // string(20)  not_null
    var $period2;                         // string(20)  not_null
    var $period3;                         // string(20)  not_null
    var $amount1;                         // real(12)  not_null
    var $amount2;                         // real(12)  not_null
    var $amount3;                         // real(12)  not_null
    var $recurring;                       // int(4)  not_null
    var $reattempt;                       // int(4)  not_null
    var $ipn_test;                        // int(4)  not_null
    var $retry_at;                        // string(50)  multiple_key
    var $recur_times;                     // int(6)  not_null
    var $username;                        // string(25)  
    var $password;                        // string(20)  
    var $subscr_id;                       // string(20)  
    var $entirepost;                      // blob(65535)  blob
    var $paypal_verified;                 // string(16)  not_null set
    var $verify_sign;                     // string(125)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Accounting_paypal',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
