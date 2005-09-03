<?php
/**
 * Table Definition for companies
 */
require_once 'DB/DataObject.php';

class Companies extends DB_DataObject 
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
	var $fb_fieldsToRender = array (
		'company_name' ,
		'last_name' ,
		'first_name',
		'salutation',
		'title' ,
		"address1", 
		"address2" ,
		"city" ,
		"state" ,
		"zip" ,
		"country", 
		"phone" ,
		"fax" ,
		"url",
		"email_address" ,
		'do_not_contact' ,
		"territory_id" ,
		"flyer_ok" 
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


var $fb_shortHeader = 'Contacts';


    function fb_display_view()
        {
            $this->orderBy('company_name, last_name');
                        
            return $this->CoopView->oneLineTable();

        }

   var $fb_sizes = array(
     'salutation' => 20,
     'address1' => 50,
     'city' => 15,
     'state' => 5,
     'zip' => 8,
     'country' => 10
   );


function fb_display_details()
        {

            // use names from old code. easier than search/replacing.
            $cp =& $this->CoopView->page;
            $top =& $this->CoopView;
            $mi = $top->pk;
            $cid = $this->{$mi};
            
            //confessObj($this, 'this');

	//$res .= "CHECKING $table<br>";
	$top->obj->$mi = $cid;
	$top->obj->find(true);		//  XXX aack! need this for summary
	$res .= $top->horizTable();

	// sponsorships
	$sp = new CoopView(&$cp, 'sponsorships', &$top);
	$sp->obj->$mi = $cid;
	$sp->obj->orderBy('school_year desc');
	$res .= $sp->simpleTable();

	foreach(array('flyer_deliveries', 
				  'solicitation_calls', 'ads') as $table)
	{
		$view = new CoopView(&$cp, $table, &$top);
		//$res .= "CHECKING $table<br>";
		if(in_array('school_year', array_keys(get_object_vars($view->obj)))){
			$view->obj->orderBy('school_year desc');
		}
		$view->obj->$mi = $cid;
		$res .= $view->simpleTable();
	}

	// XXX this just SXCREAMS for a refactoring. a repetitive function.
	// linktable, destinationtable, mainindex, and id?
	$co = new CoopObject(&$cp, 'companies_income_join', &$top);
	$co->obj->$mi = $cid;
	$real = new CoopView(&$cp, 'income', &$co);
	$real->obj->orderBy('school_year desc');
	$real->obj->joinadd($co->obj);
	//$real->obj->fb_fieldsToRender[] = 'family_id';
	$res .= $real->simpleTable();
	

	$co = new CoopObject(&$cp, 'companies_auction_join', &$top);
	$co->obj->$mi = $cid;
	$real = new CoopView(&$cp, 'auction_donation_items', &$top);
	$real->obj->orderBy('school_year desc');
	$real->obj->joinadd($co->obj);
	$res .= $real->simpleTable();

	$co = new CoopObject(&$cp, 'companies_in_kind_join', &$top);
	$co->obj->$mi = $cid;
	$real = new CoopView(&$cp, 'in_kind_donations', &$co);
	$real->obj->joinadd($co->obj);
	$res .= $real->simpleTable();

	//TODO put tickets in here!


	$inv =& new CoopObject(&$cp, 'springfest_attendees', &$top);
	$inv->obj->$mi = $cid;
	$inc = new CoopView(&$cp, 'auction_purchases', &$top);
	$inc->obj->joinAdd($inv->obj);
	$res .= $inc->simpleTable();


	// standard audit trail, for all details
	$aud =& new CoopView(&$cp, 'audit_trail', &$top);
	$aud->obj->table_name = $top->table;
	$aud->obj->index_id = $cid;
	$aud->obj->orderBy('updated desc');
	$res .= $aud->simpleTable();
    return $res;

        }


}
