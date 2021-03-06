<?php
/**
 * Table Definition for territories
 */
require_once 'DB/DataObject.php';

class Territories extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'territories';                     // table name
    var $territory_id;                    // int(32)  not_null primary_key unique_key auto_increment
    var $description;                     // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Territories',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array ('description');
	var $fb_fieldLabels = array (
		'territory_id' => 'Territory ID',
		'description' => 'Territory Name');
    var $fb_formHeaderText =  'Springfest Solicitation Territories';
    var $fb_shortHeader = 'Territories';
    var $fb_allYears = 1;
    var $dupeIgnore = array('territory_id');

// can only do these crosslinks when there is an intermediate table. sad. :-(
// 	var $fb_crossLinks = array(array('table' => 'companies', 
// 									 'toTable' => 'companies',
// 									 'toField' => 'company_id',
// 									 'type' => 'select'));


    // TODO: a summary, showing number of companies in each terrotiry

    function fb_display_details(&$co)
        {
            $res = "";
            $res .= $co->horizTable(true);

            $view =& new CoopView(&$co->page, 'companies', &$co);
            $view->obj->fb_formHeaderText = 'Solicitation Summary by Company';
            $view->obj->fb_fieldsToRender = array(); // bah, i must have crap in there
            unset($view->obj->fb_pager);
            $view->obj->fb_fieldLabels= array(
                'company_label' => 'Company',
                'cash_donations' => 'Cash Donations',
                'auction_purchases' => 'Auction Purchases',
                'auction_donations' => 'Auction Donations',
                'in_kind_donations' => 'In-Kind Donations');
            $view->obj->preDefOrder = array_keys($view->obj->fb_fieldLabels);

            $schoolyear = $co->getChosenSchoolYear(true);
            $view->obj->query(
                sprintf(" 
select %s, companies.company_id,
        sum(inc.payment_amount) as cash_donations,
        sum(pur.payment_amount) as auction_purchases,
        sum(auct.item_value) as auction_donations,
        sum(iks.item_value) as in_kind_donations
from companies
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year like '%s'
        group by caj.company_id) 
    as auct
        on auct.company_id = companies.company_id
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year like '%s'
        group by cikj.company_id) 
    as iks
        on iks.company_id = companies.company_id
left join 
    (select  sum(payment_amount) as payment_amount, company_id
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year like '%s'
        group by cinj.company_id) 
    as inc
        on inc.company_id = companies.company_id
left join 
    (select  payment_amount, company_id
     from springfest_attendees as atd
    left join auction_purchases  as ap
            on ap.springfest_attendee_id = 
                atd.springfest_attendee_id
     left join income 
              on ap.income_id = 
                income.income_id
        where income.school_year like '%s'
        group by atd.company_id) 
    as pur
        on pur.company_id = companies.company_id
where companies.territory_id = %d
group by companies.company_id
order by cash_donations desc, auction_purchases desc, 
    auction_donations desc, in_kind_donations desc ,
companies.company_name, companies.last_name
",
                        $view->obj->fb_labelQuery,
                        $schoolyear,$schoolyear, $schoolyear, $schoolyear,
                        $this->{$co->pk}
                    ));
            $res .= $view->simpleTable(false, true);



            // standard audit trail, for all details
            $aud =& new CoopView(&$co->page, 'audit_trail', &$co);
            $aud->obj->table_name = $co->table;
            $aud->obj->index_id = $this->{$co->pk};
            $aud->obj->orderBy('updated desc');
            $res .= $aud->simpleTable(true, true);

            return $res;
        }

    function fb_display_view(&$co)
        {
            $co->schoolYearChooser();
            $schoolyear = $co->getChosenSchoolYear(); // HACK from report
            $co->obj->fb_fieldsToRender = array(); // bah, i must have crap in there
            $co->obj->fb_fieldLabels= array(
                'territory' => 'Territory',
                'cash_donations' => 'Cash Donations',
                'auction_purchases' => 'Auction Purchases',
                'auction_donations' => 'Auction Donations',
                'in_kind_donations' => 'In-Kind Donations',
                'companies' => 'Number of Companies');
            $co->obj->query(
                "
select territories.description as territory, territories.territory_id,
count(companies.company_id) as companies,
        sum(inc.payment_amount) as cash_donations,
        sum(pur.payment_amount) as auction_purchases,
        sum(auct.item_value) as auction_donations,
        sum(iks.item_value) as in_kind_donations
from territories
left join companies 
    on companies.territory_id = territories.territory_id
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year like '$schoolyear'
        group by caj.company_id) 
    as auct
        on auct.company_id = companies.company_id
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year like '$schoolyear'
        group by cikj.company_id) 
    as iks
        on iks.company_id = companies.company_id
left join 
    (select  sum(payment_amount) as payment_amount, company_id
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year like '$schoolyear'
        group by cinj.company_id) 
    as inc
        on inc.company_id = companies.company_id
left join 
    (select  payment_amount, company_id
     from springfest_attendees as atd
    left join auction_purchases  as ap
            on ap.springfest_attendee_id = 
                atd.springfest_attendee_id
     left join income 
              on ap.income_id = 
                income.income_id
        where income.school_year like '$schoolyear'
        group by atd.company_id) 
    as pur
        on pur.company_id = companies.company_id
group by territories.territory_id
having cash_donations > 0 or auction_purchases > 0 or auction_donations > 0 or in_kind_donations > 0
order by cash_donations desc, auction_purchases desc, 
    auction_donations desc, in_kind_donations desc 
");
            return $co->simpleTable(false, true);


        }


}
