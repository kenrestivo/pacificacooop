<?php
/**
 * Table Definition for sponsorships
 */
require_once 'DB/DataObject.php';

class Sponsorships extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'sponsorships';                    // table name
    var $sponsorship_id;                  // int(32)  not_null primary_key unique_key auto_increment
    var $company_id;                      // int(32)  
    var $lead_id;                         // int(32)  
    var $sponsorship_type_id;             // int(32)  
    var $entry_type;                      // string(9)  enum
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sponsorships',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('entry_type');
	var $fb_formHeaderText =  'Springfest Sponsorships';
    var $fb_shortHeader = 'Sponsorships';
	var $fb_linkDisplayFields = array('company_id', 'lead_id',
									  'school_year');
	var $fb_fieldLabels = array (
		'company_id' => 'Company Name',
		'lead_id' => 'Invitee Name',
		'sponsorship_type_id' => 'Sponsorship Package',
		'entry_type' => 'Entry Control',
		'school_year' => 'School Year'
		);
	
	var $fb_requiredFields = array (
		'sponsorship_type_id', 
		'entry_type',
		'school_year' 
		);


	var $preDefOrder = array (
		'company_id',
		'lead_id' ,
		'sponsorship_type_id' ,
		'entry_type' ,
		'school_year' 
		);

    function fb_linkConstraints(&$co)
		{
            $type =& new CoopObject(&$co->page, 'sponsorship_types', 
                                    &$co);
            $co->protectedJoin($type);
            $companies =& new CoopObject(&$co->page, 'companies', 
                                         &$co);
            $co->protectedJoin($companies);
            $leads =& new CoopObject(&$co->page, 'leads', 
                                     &$co);
            $co->protectedJoin($leads);

            // TODO: somehow make orderbylinkdisplay() recursive
            $co->obj->orderBy('sponsorship_types.sponsorship_price desc, concat(companies.company_name, leads.company), concat(companies.last_name, leads.last_name)');
            $co->constrainSchoolYear();
            $co->grouper();
            
		}


    function fb_display_view(&$co)
        {
            $companies =& new CoopObject(&$co->page, 'companies', &$co);
            $co->obj->selectAdd($companies->obj->fb_labelQuery);
            $leads =& new CoopObject(&$co->page, 'leads', &$co);
            $co->obj->selectAdd($leads->obj->fb_labelQuery);
            $co->obj->fb_fieldsToUnRender = array('company_id', 'lead_id');
            $co->obj->fb_fieldLabels['company_label'] = 'Company Name';
            $co->obj->fb_fieldLabels['lead_label'] = 'Invitee';
            array_unshift($co->obj->preDefOrder,'company_label', 'lead_label');
            return $co->simpleTable();
        }



    function _onlyOne($vars)
        {
            // AHA! need to prependtable!
            // XXX need to get a coopobject in here somehow

            $count = 0;
            foreach(array($vars['sponsorships-lead_id'],
                          $vars['sponsorships-company_id']) 
                    as $val)
            {
                if($val > 0){
                    $count++;
                }

            }
            if($count > 1){
                $msg = "You can have an Invitee Name, or a Company Name, but not both.";    
                $err['sponsorships-lead_id'] = $msg;
                $err['sponsorships-company_id'] = $msg;
                return $err;
            }
            
            if($count < 1){
                $msg = "You must have either an Invitee Name, or a Company Name.";
                $err['sponsorships-lead_id'] = $msg;
                $err['sponsorships-company_id'] = $msg;
                return $err;
            }
            
            return true; 				// copacetic
        }
    
    function postGenerateForm(&$form)
        {
            $form->addFormRule(array($this, '_onlyOne'));
            $el =& $form->getElement($form->CoopForm->prependTable('entry_type'));
            $el->setValue('Manual');
 
        }

    // XXX WHACK THIS and use css and templates instead.
    // this should be deprecieated. because it sucks.
    function public_sponsors(&$co, $sy)
        {
            $cp =& $co->page; // lazy
            $co->chosenSchoolYear = $sy; ///XXX nasty hack

            // now a word from our sponsors
            $res .= '<div class="sponsor">';
            $res .= "<p><b>Thanks to our generous sponsors:</b></p>";

            $spons = $this->public_sponsors_structure(&$co);
            
            // XXX this is the stuff that needs to be done with PHPTAL instead
            foreach($spons as $level => $data){
                foreach($data['names'] as $val){
                    $sponsors .= $val['url'] ? 
                        sprintf('<li><a href="%s">%s</a></li>',
                                $val['url'], 
                                $val['name']) : 
                        sprintf("<li>%s</li>", $val['name']);
                }
                $res .= sprintf(
                    '<p><b>%s Contributors</b><br /><span class="small">($%.0f and above)</span></p><ul>%s</ul>', 
                    $level, $data['price'], $sponsors);
                $sponsors ='';
            }
	
            $res .= "</div><!-- end sponsor -->";
            return $res;
        } // end sponsors


    function public_sponsors_structure(&$co)
        {
            $co->obj->query(sprintf('select if(companies.listing is not null, 
        companies.listing,
        if(coalesce(companies.company_name, leads.company) is not null,
            coalesce(companies.company_name, leads.company), 
            concat_ws(" ", coalesce(leads.first_name, companies.first_name),
                    coalesce(leads.last_name, companies.last_name)))) 
        as sponsor_formatted,
    companies.url,
    sponsorship_types.sponsorship_name, sponsorship_types.sponsorship_price
from sponsorships
left join sponsorship_types 
    on sponsorship_types.sponsorship_type_id = sponsorships.sponsorship_type_id
left join companies on companies.company_id = sponsorships.company_id
left join leads on leads.lead_id = sponsorships.lead_id
where sponsorships.school_year = "%s"
order by sponsorship_price desc, 
if(companies.listing is not null, companies.listing, 
    if(coalesce(companies.company_name, leads.company) is not null, 
    coalesce(companies.company_name, leads.company),
    coalesce(leads.last_name, companies.last_name))) asc',
                                    $co->getChosenSchoolYear()));
            $res = array();
            while($co->obj->fetch()){
                $res[$co->obj->sponsorship_name]['price'] = 
                    $co->obj->sponsorship_price;
                $res[$co->obj->sponsorship_name]['names'][] = 
                    array('name' =>$co->obj->sponsor_formatted,
                          'url' => $co->obj->url > '' ? $co->page->fixURL($co->obj->url) : false);
            }
            $co->page->confessArray($res, 'sponsorship structure', 4);
            return $res;
        }

 

}
