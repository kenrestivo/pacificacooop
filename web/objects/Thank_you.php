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

    var $fb_displayCallbacks = array(
        'items' => 'webFormatGroupConcat',
        'value_received' => 'webFormatGroupConcat');


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

            return ""; //XXX TEMP!

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


			//TODO: mark as sent! i'll need this for invitations too
            return javaPopup() .
				$co2->page->selfURL(
					array('value' => 
						  '<img style="border:0"  src="/images/printer.png" 
								alt="Print Letters">&nbsp;Print All',
						  'base' =>'print_popup.php', 
						  'inside' => array('thing' => 'letters',
											'set' => 'needed'),
                          'title' => 'Prints all letters which have not yet been sent',
						  'popup' => true,
						  'par' => false)) . '&nbsp;(NOTE: may take several minutes to run)<br />'.
                $co2->simpleTable(false, true) ;
        }





    function fb_display_view(&$co)
        {
            require_once('ThankYou.php');
    
            $co->schoolYearChooser();

            $co->actionnames['confirmdelete'] = 'Un-Send';
            $co->actionnames['delete'] = 'Un-Send';


//             //before i go to crazy here, let's fix any orphans
//             $ty = new ThankYou(&$co->page);
//             $ty->repairOrphaned();
            
            $this->fetchTemplate(&$co);

            $co->queryFromFile('recover_thank_yous.sql');

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


function fetchTemplate(&$co)
        {
            $this->_thank_you_template = new CoopObject(&$co->page, 
                                 'thank_you_templates', &$co);
            $this->_thank_you_template->obj->whereAdd(
                sprintf('school_year = "%s"', 
                        $co->getChosenSchoolYear()));
            $this->_thank_you_template->obj->find(true);

            $co->obj->query(
                sprintf(
                    'set @school_year := "%s",
@ticket_price := %d,
@ad_text := "%s",
@ticket_text := "%s",
@cash_text := "%s"
',
                    $co->getChosenSchoolYear(),
                    COOP_SPRINGFEST_TICKET_PRICE, //XXX NOT GLOBAL!
                    $this->_thank_you_template->obj->ad,
                    $this->_thank_you_template->obj->ticket,
                    $this->_thank_you_template->obj->cash
                    ));

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
            return  preg_replace("/\n,/",'<br>',$val); 
        }

}
