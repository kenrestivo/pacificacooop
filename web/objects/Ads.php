<?php
/**
 * Table Definition for ads
 */
require_once 'DB/DataObject.php';

class Ads extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'ads';                             // table name
    var $ad_id;                           // int(32)  not_null primary_key unique_key auto_increment
    var $ad_description;                  // string(255)  
    var $ad_copy;                         // blob(16777215)  blob
    var $artwork_provided;                // string(7)  enum
    var $school_year;                     // string(50)  
    var $ad_size_id;                      // int(32)  not_null
    var $income_id;                       // int(32)  
    var $lead_id;                         // int(32)  not_null
    var $artwork_received;                // date(10)  
    var $family_id;                       // int(32)  
    var $company_id;                      // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Ads',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array ('ad_description');
	var $fb_textFields = array ('ad_copy');
	var $fb_enumFields = array ('artwork_provided');
}
