<?php
/**
 * Table Definition for leads
 */
require_once 'DB/DataObject.php';

class Leads extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'leads';                           // table name
    var $lead_id;                         // int(32)  not_null primary_key unique_key auto_increment
    var $last_name;                       // string(255)  
    var $first_name;                      // string(255)  
    var $salutation;                      // string(50)  
    var $title;                           // string(255)  
    var $company;                         // string(255)  
    var $address1;                        // string(255)  
    var $address2;                        // string(255)  
    var $city;                            // string(255)  
    var $state;                           // string(255)  
    var $zip;                             // string(255)  
    var $country;                         // string(255)  
    var $phone;                           // string(255)  
    var $relation;                        // string(8)  enum
    var $source;                          // string(10)  enum
    var $family_id;                       // int(32)  
    var $do_not_contact;                  // date(10)  
    var $school_year;                     // string(50)  
    var $source_id;                       // int(32)  
    var $fax;                             // string(255)  
    var $email_address;                   // string(255)  
    var $company_id;                      // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Leads',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_fieldsToRender = array( 'lead_id', 'last_name', 'first_name',
									'salutation', 'title', 'company',
									'address1', 'address2', 'city', 'state',
									'zip', 'country', 'phone', 
									'do_not_contact');
	var $fb_enumFields = array ('relation', 'source'); // make this a link
	var $fb_linkDisplayFields = array ('last_name', 'first_name', 'address1');
	var $fb_selectAddEmpty = array ('source_id', 'family_id');
	var $fb_fieldLabels = array( 'lead_id' => "Response Code", 
								 'last_name' => "Last Name", 
								 'first_name' => "First Name",
									'salutation' => "Mr. Ms. Dr.", 
								 'title' => "Title", 
								 'company' => "Company Name",
									'address1' => "Address", 
								 'address2' => "Address(continued)", 
								 'city' => "City", 
								 'state' => "State",
									'zip'=> "Zip Code", 
								 'country' => "Country", 
								 'phone' => "Phone Number", 
									'do_not_contact'=> "Do Not Contact After");
//	var $fb_preGenerateForm
}
