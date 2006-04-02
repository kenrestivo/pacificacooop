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
                             

    // manually whack 'add'. 'enter new' is NOT appropriate for this
    var $viewActions = array('view'=> ACCESS_VIEW);     
    

//     function fb_linkConstraints(&$co)
// 		{
//             $co->buildConstraintsFromJoinPaths();
            
//         }



// silly utility function this is really format-specific
function _detailsLink(&$co, $current)
        {
            $res = "";
            $tmptab = $current['id_name'] == 'company_id' ?
                'companies': 'leads';


            // XXX cheap subset of CoopView::recordButtons()
            // TODO: do it right and instantiate or fake recordButtons()
            $res .= $co->page->selfURL(
                array('value' => 'Details',
                      'base' => 'generic.php',
                      'inside' => array('table' => $tmptab,
                                        $tmptab . '-' . 
                                        $current['id_name'] => $current['id'],
                                        'action' => 'details',
                                        'push' => 'thank_you')));

            return $res;

        }


function thanksNeededPickList(&$co)
        {

            // this is pseudo-templating, using htmltable
            $tab = new HTML_Table();	
                $tab->addRow(
                    array(
                        'Address on Envelope and Letter',
                        'Dear:',
                        'Thank you for your kind donation of:',
                        'In exchange for your contribution, we gave you:',
                        'Sincerely,',
                        'Actions'),
                    'class="tableheaders"', 'TH');

            foreach($this->thanksNeededSummary(&$co) as $ty){
                // XXX note, this is not valid xhtml: li's are not closed
                $tab->addRow(
                    array($ty['name'] . '<br />'. 
                          implode('<br />', $ty['address_array']),
                          $ty['dear'],
                          implode('<br />', $ty['items_array']),
                          implode('<br />', $ty['value_received_array']),
                          $ty['from'],
                          $this->_detailsLink(&$co, $ty)
                          ));
            }

            $tab->altRowAttributes(1, 'class="altrow1"', 
                                   'class="altrow2"');

			//TODO: mark as sent! i'll need this for invitations too
            return javaPopup() .
				$co->page->selfURL(
					array('value' => 
						  '<img style="border:0"  src="/images/printer.png" 
								alt="Print Letters">&nbsp;Print Letters',
						  'base' =>'print_popup.php', 
						  'inside' => array('thing' => 'letters',
											'set' => 'needed'),
						  'popup' => true,
						  'par' => false)) .
                $tab->toHTML() ;
        }





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
left join companies_income_join on companies_income_join.income_id = income.income_id
left join companies on companies_income_join.company_id = companies.company_id
left join leads_income_join on leads_income_join.income_id = income.income_id
left join leads on leads.lead_id = leads_income_join.lead_id
where coalesce(auction_donation_items.school_year, in_kind_donations.school_year, income.school_year) = "%s"
order by concat(coalesce(leads.last_name, companies.last_name), coalesce(leads.first_name, companies.first_name), coalesce(leads.company, companies.company_name))
',
                    $co->getChosenSchoolYear()));

// TODO: join to everyone, and grab the recipient, items, salesperson
//


            //before i go to crazy here, let's fix any orphans
            $ty = new ThankYou(&$co->page);
            $ty->repairOrphaned();


            return '<h3>The following thank you notes need to be sent:</h3>'.
                $this->thanksNeededPickList(&$co) . 
                '<h3>Thank you notes below have already been sent:</h3>'.
                $co->simpleTable(false,true);

        }



function thanksNeededSummary($co, $format = 'array')
        {

            $sy= $co->getChosenSchoolYear();

            $top = new CoopView(&$co->page, 'companies', $nothing);
            
            $top->obj->query("
select concat_ws(' - ', company_name, concat_ws(' ', first_name, last_name)) 
    as Company, 
        companies.company_id as id, 'company_id' as id_name,
        coalesce(sum(inc.payment_amount),0) +
        coalesce(sum(pur.payment_amount),0) + 
        coalesce(sum(auct.item_value),0) + 
        coalesce(sum(iks.item_value),0) 
        as Total
from companies
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_auction_join  as caj
     left join auction_donation_items  as adi
              on caj.auction_donation_item_id = 
                adi.auction_donation_item_id
        where school_year = '$sy' 
        and adi.date_received > '2000-01-01'
        and adi.thank_you_id is null
        group by caj.company_id) 
    as auct
        on auct.company_id = companies.company_id
left join 
    (select  sum(item_value) as item_value, company_id
     from companies_in_kind_join as cikj
     left join in_kind_donations as ikd
              on cikj.in_kind_donation_id = 
                ikd.in_kind_donation_id
        where school_year = '$sy'
        and ikd.date_received > '2000-01-01'
        and ikd.thank_you_id is null
        group by cikj.company_id) 
    as iks
        on iks.company_id = companies.company_id
left join 
    (select  sum(payment_amount) as payment_amount, company_id
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '$sy'  $cy
        and income.thank_you_id is null
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
        where income.school_year = '$sy'  $cy
        and income.thank_you_id is null
        group by atd.company_id) 
    as pur
        on pur.company_id = companies.company_id
group by companies.company_id
having Total > 0
UNION DISTINCT
select concat_ws(' - ', concat_ws(' ', first_name, last_name), company ) 
    as Company, 
        leads.lead_id as id, 'lead_id' as id_name,
        coalesce(sum(tic.total),0) + coalesce(sum(inc.total),0) as Total
from leads
left join 
    (select lead_id, sum(payment_amount) as total
     from leads_income_join as linj
     left join income 
              on linj.income_id = 
                income.income_id
        where income.school_year = '$sy' $cy
        and income.thank_you_id is null
        group by linj.lead_id) 
    as inc
        on leads.lead_id = inc.lead_id
left join 
    (select lead_id, sum(payment_amount) as total
     from tickets
     left join income 
              on tickets.income_id = 
                income.income_id
        where income.school_year = '$sy' $cy
        and income.thank_you_id is null
        group by tickets.lead_id) 
    as tic 
        on tic.lead_id = leads.lead_id
group by leads.lead_id
having Total > 0
order by Company
limit 20
");
            //TODO: abstract this out, will need it in several places
            $res = array() ;
            $total = $top->obj->N;
            while($top->obj->fetch()){
                $count++;
                user_error("thanksNeededSummary($format) $count of $total", 
                           E_USER_NOTICE);
                $ty =& new ThankYou(&$co->page);
                if(!$ty->findThanksNeeded($top->obj->id_name, $top->obj->id)){
                    //skip the ones in the query that don't cut it in here
                    continue;
                }
                switch($format){
                    //TODO: email too
                case 'pdml':
                    $ty->substitute(); // only need this for formatted stuff
                    $res[] =  $ty->toHTML();
                    break;
                case 'array':
                default:
                    $res[] = array_merge($top->obj->toArray(), 
                                         get_object_vars($ty));
                    break;
                }

            }
            
            //$co->page->confessArray($res, 'the total result', 4);
            return $res;
        }



}
