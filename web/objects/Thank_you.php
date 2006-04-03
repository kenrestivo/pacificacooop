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

    var $fb_displayCallbacks = array('items' => 'webFormatGroupConcat');


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


            $res .= $co->page->selfURL(
					array('value' => 
						  'Print/Preview',
						  'base' =>'print_popup.php', 
						  'inside' => array(
                              'thing' => 'letters',
                              'set' => 'one',
                              'pk' => $current['id_name'],
                              'id' => $current['id']),
						  'popup' => true,
						  'par' => false)) ;


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
								alt="Print Letters">&nbsp;Print All',
						  'base' =>'print_popup.php', 
						  'inside' => array('thing' => 'letters',
											'set' => 'needed'),
                          'title' => 'Prints all letters which have not yet been sent',
						  'popup' => true,
						  'par' => false)) . '&nbsp;(NOTE: may take several minutes to run)<br />'.
                $tab->toHTML() ;
        }





    function fb_display_view(&$co)
        {
            require_once('ThankYou.php');
    
            $co->schoolYearChooser();

            $co->actionnames['confirmdelete'] = 'Un-Send';
            $co->actionnames['delete'] = 'Un-Send';


            //before i go to crazy here, let's fix any orphans
            $ty = new ThankYou(&$co->page);
            $ty->repairOrphaned();

            //TODO: move this massive query to an include file
            // a .sql file so it looks reasonable in emacs

            $co->obj->query(
                sprintf(
'select distinct thank_you.*,
coalesce(auction_summary.school_year, in_kind_summary.school_year, 
    income_summary.school_year) as school_year,
concat_ws("\n", coalesce(leads.company, companies.company_name), 
    concat_ws(" ", coalesce(leads.first_name, companies.first_name),
        coalesce(leads.last_name, companies.last_name))) as recipient,
concat_ws(" ", working_parents.first_name, working_parents.last_name) 
    as salesperson,
concat_ws("\n", concat("$", income_summary.total_payment, " cash"),
            auction_summary.short_descriptions, 
            in_kind_summary.item_descriptions) as items
from thank_you
left join (select group_concat(auction_donation_items.short_description, "\n")
                    as short_descriptions, 
                auction_donation_items.thank_you_id,
                auction_donation_items.school_year,
                companies_auction_join.family_id, 
                companies_auction_join.company_id
            from auction_donation_items 
                left join companies_auction_join 
                    on companies_auction_join.auction_donation_item_id = 
                        auction_donation_items.auction_donation_item_id
            group by auction_donation_items.thank_you_id) as auction_summary
       on thank_you.thank_you_id = auction_summary.thank_you_id
left join (select group_concat(in_kind_donations.item_description, "\n")
                    as item_descriptions,
                in_kind_donations.thank_you_id,
                in_kind_donations.school_year,
                companies_in_kind_join.family_id, 
                companies_in_kind_join.company_id
            from in_kind_donations
            left join companies_in_kind_join 
                on companies_in_kind_join.in_kind_donation_id = 
                    in_kind_donations.in_kind_donation_id
            group by in_kind_donations.thank_you_id) as in_kind_summary 
    on thank_you.thank_you_id = in_kind_summary.thank_you_id
left join (select sum(income.payment_amount) as total_payment,
            income.thank_you_id, income.school_year,
             coalesce(leads_income_join.lead_id, tickets.lead_id) as lead_id,
            companies_income_join.company_id, companies_income_join.family_id
            from income 
              left join companies_income_join 
                  on companies_income_join.income_id = income.income_id
              left join leads_income_join 
                    on leads_income_join.income_id = income.income_id
            left join tickets on tickets.income_id = income.income_id
            group by income.thank_you_id)
            as income_summary
    on thank_you.thank_you_id = income_summary.thank_you_id
left join companies 
    on coalesce(income_summary.company_id, 
        auction_summary.company_id, in_kind_summary.company_id) 
            = companies.company_id
left join leads 
    on income_summary.lead_id = leads.lead_id
left join (select parents.* from parents 
            left join workers on parents.parent_id = workers.parent_id
            where workers.parent_id is not null
            group by parents.family_id) as working_parents
        on working_parents.family_id = 
        coalesce(income_summary.family_id, 
                auction_summary.family_id)
where (auction_summary.school_year = "%s" 
    or in_kind_summary.school_year = "%s" 
    or income_summary.school_year = "%s") 
order by if(coalesce(companies.company_name, leads.company) is not null
            and coalesce(companies.company_name, leads.company) > "", 
            coalesce(companies.company_name, leads.company), 
        concat(coalesce(leads.last_name, companies.last_name),
                coalesce(leads.last_name, companies.last_name)))
',
                    $co->getChosenSchoolYear(),
                 $co->getChosenSchoolYear(),
                 $co->getChosenSchoolYear()));

            $this->fb_fieldLabels = array_merge(
                $this->fb_fieldLabels,
                array('items' => 'Items',
                      'recipient' => 'Recipient',
                      'salesperson' => 'Salesperson'));

            $this->preDefOrder = array( 'recipient', 'items', 'salesperson',
                                       'date_sent', 'method', 'family_id');


            return '<h3>The following thank you notes need to be sent:</h3>'.
                $this->thanksNeededPickList(&$co) . 
                '<h3>Thank you notes below have already been sent:</h3>'.
                $co->simpleTable(false,true);

        }



function thanksNeededSummary($co, $format = 'array')
        {

            $sy= $co->getChosenSchoolYear();

            $top = new CoopView(&$co->page, 'companies', $nothing);

            // XXX hack for testing
            if(devSite()){
                //$limit = 'limit 20';
            }


            //TODO: move this massive query to an include file
            // a .sql file so it looks reasonable in emacs

            
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
$limit
");
            //TODO: abstract this out, will need it in several places
            $res = array() ;
            $total = $top->obj->N;
            while($top->obj->fetch()){
                $count++;
                $co->page->printDebug("thanksNeededSummary($format) $count of $total", 
                           4);
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


    function webFormatGroupConcat(&$co, $val, $key)
        {
            // takes a field with items delimited by \n and by a leading ,
            // and makes them pretty for the web with a chr(183) delimiter
            return  preg_replace("/\n,/",'<br>'.chr(183),$val); 
        }

}
