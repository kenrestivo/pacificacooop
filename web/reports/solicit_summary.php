<?php

	#  Copyright (C) 2004  ken restivo <ken@restivo.org>
	# 
	#  This program is free software; you can redistribute it and/or modify
	#  it under the terms of the GNU General Public License as published by
	#  the Free Software Foundation; either version 2 of the License, or
	#  (at your option) any later version.
	# 
	#  This program is distributed in the hope that it will be useful,
	#  but WITHOUT ANY WARRANTY; without even the implied warranty of
	#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	#  GNU General Public License for more details. 
	# 
	#  You should have received a copy of the GNU General Public License
	#  along with this program; if not, write to the Free Software
	#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

//$Id$


require_once('../first.inc');
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');



//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();


$atd = new CoopView(&$cp, 'companies', $none);

print $cp->topNavigation();
print $cp->stackPath();

print "\n<hr /></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div class="centerCol">';

function schoolYearChooser(&$atd, $table)
{
    $res ='';
    
    $at = new CoopView(&$atd->page, $table, $none);
 
    $syform =& new HTML_QuickForm('schoolyearchooser', false, false, 
                                  false, false, true);
    $syform->removeAttribute('name');
    $el =& $syform->addElement('select', 'gschoolyear', 'Choose School Year', 
                               //TODO check ispermittedfield for allyears!
                               $at->getSchoolYears(null, true),
                               array('onchange' =>'this.form.submit()'));

    if($sid = thruAuthCore($at->page->auth)){
        $syform->addElement('hidden', 'coop', $sid); 
    }

    $syform->setDefaults(array('gschoolyear' => $at->page->currentSchoolYear));

    $res .= $syform->toHTML();
    
    $foo = $el->getValue();
    $schoolyear=$foo[0];

    return array($schoolyear, $res);
}





function viewHack(&$atd)
{

    $res = '';

    list($schoolyear, $chooser) = schoolYearChooser(&$atd, 'income');

    $res .= $chooser;

    // XXX nastiest fuckign hack on earth!
    // force it to not show the goddamned popup by picking something
    // that isn't elsewhere below, making it isTop()
    $faketop =& new CoopView(&$atd->page, 'income', &$nothing);
    $faketop->chosenSchoolYear = $schoolyear; // hack!

    $res .=  sprintf('<h2>Solicitation totals for %s </h2>',
                     $schoolyear == '%' ? 'All Years Combined' : $schoolyear);

	$res .= showRawQuery("Solicitation Cash Income by Type",
" 
select coa.description as Description,
        sum(inc.total) as Before_Event, 
        sum(pur.total) as At_Event
from chart_of_accounts as coa
left join 
    (select account_number, sum(payment_amount) as total
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year like '$schoolyear'
        group by cinj.company_id) 
    as inc
        on inc.account_number = coa.account_number
left join 
    (select  account_number, payment_amount as total
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
        on pur.account_number = coa.account_number
group by coa.account_number
having Before_Event >0 or At_Event > 0
order by Before_Event desc, At_Event desc
"
					   ,1);



    $view =& new CoopView(&$atd->page, 'families', &$faketop);
    $view->chosenSchoolYear = $schoolyear; // hack!
	$view->obj->fb_formHeaderText = 'Top Soliciting Families';
    $view->obj->fb_fieldsToRender = array(); // bah, i must have crap in there
    $view->obj->fb_fieldLabels= array(
        'Soliciting_family' => 'Soliciting Family',
        'cash_donations' => 'Cash Donations',
        'auction_purchases' => 'Auction Purchases',
        'auction_donations' => 'Auction Donations',
        'in_kind_donations' => 'In-Kind Donations');
    $view->obj->query(

" 
select families.name as Soliciting_family, families.family_id,
        sum(inc.payment_amount) as cash_donations,
        sum(auct.item_value) as auction_donations,
        sum(iks.item_value) as in_kind_donations
from families
left join 
    (select  caj.family_id, sum(item_value) as item_value
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year like '$schoolyear'
        group by caj.family_id) 
    as auct
        on auct.family_id = families.family_id
left join 
    (select  cikj.family_id, sum(item_value) as item_value
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year like '$schoolyear'
        group by cikj.family_id) 
    as iks
        on iks.family_id = families.family_id
left join 
    (select  cinj.family_id, sum(payment_amount) as payment_amount
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year like '$schoolyear'
        group by cinj.family_id) 
    as inc
        on inc.family_id = families.family_id
group by families.family_id
having cash_donations > 0 or auction_donations > 0 or in_kind_donations > 0
order by cash_donations desc, 
    auction_donations desc, in_kind_donations desc 

");
    $res .= $view->simpleTable(false, true);


    
    $view =& new CoopView(&$atd->page, 'territories', &$faketop);
    $view->chosenSchoolYear = $schoolyear; // hack!
	$view->obj->fb_formHeaderText = 'Solicitation Summary by Territory';
    $res .= $view->obj->fb_display_view(&$view);




    $view =& new CoopView(&$atd->page, 'companies', &$faketop);
    $view->chosenSchoolYear = $schoolyear; // hack!
	$view->obj->fb_formHeaderText = 'Solicitation Summary by Company';

    unset($view->obj->fb_pager); // IMPORTANT! pager doesn't work here

    $view->obj->preDefOrder= array(
        'company_label' ,
        'cash_donations' ,
        'auction_purchases',
        'auction_donations',
        'in_kind_donations');
    $view->obj->fb_fieldLabels= array(
        'company_label' => 'Company',
        'cash_donations' => 'Cash Donations',
        'auction_purchases' => 'Auction Purchases',
        'auction_donations' => 'Auction Donations',
        'in_kind_donations' => 'In-Kind Donations');
    $view->obj->query(
"select {$view->obj->fb_labelQuery},
companies.company_id,
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
group by companies.company_id
having cash_donations > 0 or auction_purchases > 0 or auction_donations > 0 or in_kind_donations > 0
order by cash_donations desc, auction_purchases desc, 
    auction_donations desc, in_kind_donations desc 

");
    $res .= $view->simpleTable(false, true);


	return $res;
	 
}

// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
	 
//// EDIT AND NEW //////
 case 'new':
 case 'edit':
	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$atd);

	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SOLICITSUMMARY -->

