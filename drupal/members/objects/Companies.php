<?php
/**
 * Table Definition for companies
 */
require_once 'DB/DataObject.php';

class Companies extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'companies';                       // table name
    var $company_id;                      // int(32)  not_null primary_key unique_key auto_increment
    var $company_name;                    // string(255)  
    var $address1;                        // string(255)  
    var $address2;                        // string(255)  
    var $city;                            // string(255)  
    var $state;                           // string(255)  
    var $zip;                             // string(255)  
    var $country;                         // string(255)  
    var $phone;                           // string(255)  
    var $fax;                             // string(255)  
    var $email_address;                   // string(255)  
    var $territory_id;                    // int(32)  
    var $do_not_contact;                  // datetime(19)  binary
    var $flyer_ok;                        // string(7)  enum
    var $family_id;                       // int(32)  
    var $first_name;                      // string(255)  
    var $last_name;                       // string(255)  
    var $title;                           // string(255)  
    var $salutation;                      // string(50)  
    var $url;                             // string(255)  
    var $listing;                         // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Companies',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('company_name', 'last_name',
									  'first_name');
	var $fb_fieldLabels = array (
		'company_name' => 'Company Name',
		'listing' => 'Publicly List Company As (appears on website and in program)',
		"family_id" => 'Is this company owned by a Co-Op Family?',
		'last_name' => "Last Name", 
		'first_name' => "First Name",
		'salutation' => "Mr. Ms. Dr.", 
		'title' => "Title", 
		"address1" => "Address" ,
		"address2" => "Address2" ,
		"city" => "City" ,
		"state" => "State" ,
		"zip" => "Zip/PC" ,
		"country" => "Country" ,
		"phone" => "Phone Number" ,
		"fax" => "FAX Number" ,
		"email_address" => "Email Address" ,
		"url" => "Web Page URL" ,
		'do_not_contact' => "Don't contact after this date",
		"territory_id" => "Territory",
		"flyer_ok" => "OK to place a flyer there?" 
);
	var $fb_enumFields = array ('flyer_ok');
	var $fb_selectAddEmpty = array ('territory_id', 'family_id', 
									'do_not_contact');
	var $fb_formHeaderText =  'Springfest Solicitation Contacts';
	var $fb_URLFields = array ('url');


var $fb_requiredFields = array(
   'company_name',
   'state',
   'country'
);

var $fb_dupeIgnore = array(
   'family_id',
   'salutation',
   'title',
   'address2',
   'phone',
   'fax',
   'email_address',
   'url',
   'territory_id',
   'flyer_ok'
);

var $fb_defaults = array(
  'city' => 'Pacifica',
  'state' => 'CA',
  'zip' => 94044,
  'country' => 'USA',
  'flyer_ok' => 'Unknown'
);


	var $preDefOrder = array (
		'company_name',
        'listing',
		"family_id" ,
		'last_name',
		'first_name',
		'salutation',
		'title',
		"address1", 
		"address2",
		"city" ,
		"state" ,
		"zip" ,
		"country", 
		"phone" ,
		"fax" ,
		"email_address" ,
		"url" ,
		'do_not_contact',
		"territory_id" ,
		"flyer_ok" 
);


    var $fb_labelQuery = 'concat_ws("\n", companies.company_name, 
if(length(companies.first_name) + length(companies.last_name) > 1, 
  concat_ws(" ", companies.first_name, companies.last_name), null),
if(length(companies.address1) > 1, companies.address1, NULL), 
if(length(companies.address2) > 1, companies.address2, NULL), 
if(length(companies.city) > 1, companies.city, NULL), 
if(length(companies.phone) > 1, companies.phone, NULL), 
if(length(companies.email_address) > 1, companies.email_address, NULL))
    as company_label';
    
    var $fb_shortHeader = 'Contacts';
    

    ///XXX the auction purchases are utterly fucked up
    var $fb_extraDetails = array(
        'companies_income_join:income',
        'companies_auction_join:auction_donation_items',
        'companies_in_kind_join:in_kind_donations',
        'springfest_attendees:auction_purchases');
    
    function fb_display_view(&$co)
        {
            $co->schoolYearChooser();
            $this->selectAdd($this->fb_labelQuery);
            $this->fb_fieldLabels['company_label'] = 'Company Contact Information';
            $this->preDefOrder = array('company_label', 
                                       'listing',
                                       'url',
                                              'territory_id');

            return $co->simpleTable(true,true) . $ap;
        }

   var $fb_sizes = array(
     'salutation' => 20,
     'address1' => 50,
     'listing' => 100,
     'city' => 15,
     'state' => 5,
     'zip' => 8,
     'country' => 10
   );

    var $fb_allYears = 1;

    var $fb_pager =array('method' => 'alpha',
                           'keyname' => 'company_name');


}
