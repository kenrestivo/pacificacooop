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
	var $fb_linkDisplayFields = array ('ad_size_id' , 'ad_description');
	var $fb_textFields = array ('ad_copy');
	var $fb_enumFields = array ('artwork_provided');
	var $kr_longTitle = 'Springfest Ads';
	var $fb_fieldLabels = array(
		"company_id" => "Company Name",
		"ad_size_id" => "Ad size",
		"artwork_provided" => "Customer will provide their own artwork?",
		"artwork_received" => "Date Artwork Received (MM/DD/YYYY)" ,
		"ad_copy" => "Type Ad Copy here (if applicable)" ,
		"family_id" => "Soliciting Family",
		"school_year" => "School Year (YYYY-YYYY)" ,
		'income_id' =>  'Payment Summary',
		'lead_id' => "Invitee"
		);

	var $fb_fieldsToRender = array(
		"company_id" , "ad_size_id" ,
		"artwork_provided" , "artwork_received" , "family_id" ,
		"school_year", 'income_id' , 'lead_id'
		);

}
