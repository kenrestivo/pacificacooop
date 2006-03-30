<?php
/**
 * Table Definition for thank_you
 */
require_once 'DB/DataObject.php';

class Thank_you extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'thank_you';                       // table name
    var $thank_you_id;                    // int(32)  not_null primary_key unique_key auto_increment
    var $date_printed;                    // date(10)  binary
    var $date_sent;                       // date(10)  binary
    var $family_id;                       // int(32)  
    var $method;                          // string(7)  enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Thank_you',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
###END_AUTOCODE



	var $fb_linkDisplayFields = array('date_sent', 'method');
	var $fb_fieldLabels = array (
		'thank_you_id' => 'Thank You Note',
		'date_printed' => 'Date Printed',
		'date_sent' => 'Date Sent',
		'method' => 'Sent Via',
		'family_id' => 'Printed/Sent By'
		);

	var $fb_formHeaderText =  'Springfest Thank-You Notes';


    var $fb_shortHeader = 'Thank-You Notes';

    var $fb_dupeIgnore = array(
        'method'
        );

    var $fb_requiredFields = array(
        'method'
        );

    var $fb_joinPaths = array('school_year' => 
                              array('auction_donation_items',
                                    'in_kind_donations',
                                    'income'));
                                    
    

//     function fb_linkConstraints(&$co)
// 		{
//             $co->buildConstraintsFromJoinPaths();
            
//         }




    function fb_display_view(&$co)
        {
            require_once('ThankYou.php');
    
            $co->schoolYearChooser();
            $co->obj->query(
                sprintf(
                    'select thank_you.* ,
coalesce(auction_donation_items.school_year, in_kind_donations.school_year, income.school_year) as school_year
from thank_you
left join auction_donation_items on thank_you.thank_you_id = auction_donation_items.thank_you_id
left join income on thank_you.thank_you_id = income.thank_you_id
left join in_kind_donations on thank_you.thank_you_id = in_kind_donations.thank_you_id
where coalesce(auction_donation_items.school_year, in_kind_donations.school_year, income.school_year) = "%s"
',
                    $co->getChosenSchoolYear()));

// TODO: join to everyone, and grab the recipient, items, salesperson
// left join companies_income_join on companies_income_join.income_id = income.income_id
// left join companies on companies_income_join.company_id = companies.company_id
// left join leads_income_join on leads_income_join
//order by concat(coalesce(), coalesce(), coalesce())


            //before i go to crazy here, let's fix any orphans
            $ty = new ThankYou(&$co->page);
            $ty->repairOrphaned();


            return $co->simpleTable(false,true);

        }

}
