<?php
/**
 * Table Definition for ads
 */
require_once 'DB/DataObject.php';

class Ads extends CoopDBDO 
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
    var $lead_id;                         // int(32)  
    var $artwork_received;                // date(10)  binary
    var $family_id;                       // int(32)  
    var $company_id;                      // int(32)  
    var $_cache_ad_copy;                  // string(255)  
    var $freebie;                         // string(7)  enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Ads',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array ('ad_size_id' , 'ad_description');
	var $fb_textFields = array ('ad_copy');
	var $fb_enumFields = array ('artwork_provided', 'freebie');
	var $fb_formHeaderText = 'Springfest Ads';
	var $fb_fieldLabels = array(
		"company_id" => "Company Name",
		'lead_id' => "Invitee",
		"ad_size_id" => "Ad size",
		"artwork_provided" => "Customer will provide their own artwork?",
		"artwork_received" => "Date Artwork Received" ,
		"ad_copy" => "Type Ad Copy here (if applicable)" ,
		"family_id" => "Soliciting Family",
		"school_year" => "School Year" ,
        'freebie' => 'Comp This Ad (no payment required)',
		'income_id' =>  'Payment Summary',
		);
    


    var $fb_shortHeader = 'Ads';

    var $fb_requiredFields = array(
        'company_id',
        'ad_size_id',
        'artwork_provided',
        'family_id',
        'school_year'
        );

    var $fb_dupeIgnore = array(
        'artwork_provided',
        'ad_copy'
        );

    var $fb_defaults = array(
        'artwork_provided' => 'Yes'
        );

   var $fb_sizes = array(
     'ad_copy' => 100
   );

/// TODO: put the list of DONORS there too, or a list of them


//TODO: a form rule to either/or the company/invitee
    // better still: a choose box to pick which one


// set ad_copy lines = 3

/// XXX NOTE THIS FUNCTION NEEDS TO BE REWRITTEN!
/// it does not use the proper format for inclusion here in the dataobject
/// it needs to also return a hashtable(array) which can then be formatted
/// by the caller in whatever CSS or javascript way is needed
function public_ads(&$co, $sy)
{
	$res = "<p><b>Our advertisers:</b></p>";

    $ads = $this->public_ads_structure(&$co);
    
    $res .= "<ul>";
    foreach($ads as $ad){
        $res .= $ad['url'] 
            ? sprintf('<li><a href="%s">%s</a></li>', 
                      $ad['url'],
                      $ad['name'])
            :sprintf("<li>%s</li>", 
                     $ad['name']);
        
    }
	$res .= "</ul>";

	return $res;
}

  function public_ads_structure(&$co)
        {
            $sy = $co->getChosenSchoolYear();
            $companies =& new CoopView(&$co->page, 'companies', &$nothing);
            $companies->obj->query("select distinct * from ads left join companies on companies.company_id = ads.company_id left join sponsorships on companies.company_id = sponsorships.company_id where ads.school_year = '$sy' and sponsorship_id is null order by if(companies.listing is not null, companies.listing,companies.company_name)");
            $res = array();
                        $i = 0;
            while($companies->obj->fetch()){
                $res[$i]['name'] =  $companies->obj->listing? 
                    $companies->obj->listing : 
                    $companies->obj->company_name;
                $res[$i]['url'] = $companies->obj->url > '' ?
                    $co->page->fixURL($companies->obj->url) : false;
                $i++;
            }
            
            $co->page->confessArray($res, 'ads structure', 4);
            return $res;
        }

}
