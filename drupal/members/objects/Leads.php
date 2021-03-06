<?php
/**
 * Table Definition for leads
 */
require_once 'DB/DataObject.php';

class Leads extends CoopDBDO 
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
    var $do_not_contact;                  // date(10)  binary
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
	var $preDefOrder = array( 'lead_id', 'last_name', 'first_name', 
                                    'title', 'company',
									'salutation', 
									'address1', 'address2', 'city', 'state',
									'zip', 'country', 'phone', 
									'do_not_contact');
	var $fb_enumFields = array ('relation', 'source'); // make this a link
	var $fb_linkDisplayFields = array ('last_name', 'first_name', 'company', 
                                       'address1');
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
								 'source_id' => "Source of Contact", 
									'do_not_contact'=> "Do Not Contact After");
	var  $fb_formHeaderText = "Master Names Database";

	var $fb_hidePrimaryKey = false; // i needs my lead_id!

    var $fb_shortHeader = 'Contacts';

    var $fb_dupeIgnore = array(
        'family_id',
        'salutation',
        'title',
        'address2',
        'relation'
        );


// var $fb_joinPaths = array(
//   'school_year' => 'enrollment'
// );

    var $fb_requiredFields = array(
        'last_name',
        'address1',
        'city',
        'state',
        'zip',
        'country',
        'source_id'
        );

    var $fb_defaults = array(
        'city' => 'Pacifica',
        'state' => 'CA',
        'zip' => '94044',
        'country' => 'USA',
        'source_id' => 1
        );


   var $fb_sizes = array(
     'salutation' => 20,
     'first_name' => 50,
     'address1' => 50,
     'city' => 15,
     'state' => 5,
     'zip' => 8,
     'country' => 10
   );

    var $fb_pager =array('method' => 'alpha',
                         'keyname' => 'last_name');

    var $fb_labelQuery = 'concat_ws("\n"
,concat_ws(" " , if(leads.salutation, leads.salutation, null), leads.first_name, leads.last_name)
,if(length(leads.title)>0, leads.title, null)
,if(length(leads.company)>0, leads.company, null)
,if(length(leads.address1)>0, leads.address1, null)
,if(length(leads.address2)>0, leads.address2, null)
,concat_ws(" ", concat(leads.city, ", ", leads.state), leads.zip, if(leads.country != "USA", leads.country, ""))
) as lead_label';

	// can be called with no leadid if it's already in the object itself
	// and no find is needed
	// this is used in RSVP and tickets
	function showUser($leadid = false)
		{
			if($leadid){
				$this->lead_id = $leadid;
				$found = $this->find(true);
				if(!$found){
					return false;
				}
			}
			// ripped from thankyou, mostly. should abstract it out!
			$address_array[] = implode(' ', array($this->salutation,
												  $this->first_name,
												  $this->last_name
										   ));

			// note: it's company in leads, company_name in companies. fock.
			foreach(array('title', 'company', 'address1', 'address2') 
					as $var)
			{
				if($this->$var){
					$address_array[] = $this->$var;
				}
			}
			$address_array[] = sprintf("%s %s, %s", 
									   $this->city,
									   $this->state,
									   $this->zip);		
			$address = implode('<br />', $address_array);

			return $address;
		}

    // XXX is this cruft??? yes it probably is. make it go away.
	function constrainedInvitePopup($schoolyear = false)
		{
			$schoolyear = $schoolyear ? $schoolyear : findSchoolYear();

			// TODO compare to invites
 			$inv = DB_DataObject::factory('invitations'); 
 			if (PEAR::isError($inv)){
				user_error("Leads.php::constrainedInvitePopup(): db badness", 
						   E_USER_ERROR);
			}
			$this->joinAdd($inv);
			$this->whereAdd(sprintf('%s.school_year = "%s"',
									$inv->__table, $schoolyear));
			// TODO fix user func array here
			$this->orderBy(implode(', ' , $this->fb_linkDisplayFields));

			$this->find();
			$options[] = '-- CHOOSE ONE --';
			while($this->fetch()){
				$vals= array();
				foreach($this->fb_linkDisplayFields as $fname){
					$vals[] = $this->$fname;
				}
				$options[$this->lead_id] = 
					sprintf("%.42s...", 
							implode(' - ' , $vals));
			}
			$el =& HTML_QuickForm::createElement(
				'select', 'lead_id', 
				$this->fb_fieldLabels['lead_id'], 
				&$options);

			return $el;
		}


	function fb_linkConstraints(&$co)
		{
            // XXX ONLY if perms permit!
			$this->whereAdd('(do_not_contact is null or do_not_contact< "2000-01-01")');

            $co->orderByLinkDisplay();
            $co->grouper();
		}	

    function fb_display_view(&$co)
        {
            $res = "";
            $invites =& new CoopObject(&$co->page, 'invitations', &$co);
            $invites->linkConstraints();
            $co->protectedJoin($invites);

            $co->schoolYearChooser();


            $co->obj->selectAdd($this->fb_labelQuery);
            $this->fb_fieldLabels['lead_label'] = 'Address Label Preview';

            $this->selectAdd('leads.lead_id as response_code');
            $this->fb_fieldLabels['response_code'] = 'Response Code';
            $this->fb_fieldLabels['source_id'] = 'Source of Contact';
            $this->preDefOrder= array('lead_label', 'response_code', 
                                      'source_id');


            return  $co->simpleTable(true,true). $ap;
        }




}
