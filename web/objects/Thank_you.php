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
        'items' => 'Thank you for your kind donation of:',
        'recipient' => 'Recipient',
        'salesperson' => 'Sincerely,',
        'address' => 'Address on Envelope and Letter',
        'dear' => 'Dear:',
        'value_received' => 'In exchange for your contribution, we gave you:',
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
function recordButtons(&$co, $par, $wrap)
        {
            $res = "";
            $tmptab = $co->obj->id_name == 'company_id' ?
                'companies': 'leads';


            // XXX cheap subset of CoopView::recordButtons()
            // TODO: do it right and instantiate or fake recordButtons()
            $res .= $co->page->selfURL(
                array('value' => 'Details',
                      'base' => 'generic.php',
                      'inside' => array('table' => $tmptab,
                                        $tmptab . '-' . 
                                        $co->obj->id_name => $co->obj->id,
                                        'action' => 'details',
                                        'push' => 'thank_you'),
                      'par' => $par));


            $res .= $co->page->selfURL(
					array('value' => 
						  'Print/Preview',
						  'base' =>'print_popup.php', 
						  'inside' => array(
                              'thing' => 'letters',
                              'set' => 'one',
                              'pk' => $co->obj->id_name,
                              'id' => $co->obj->id),
						  'popup' => true,
						  'par' => $par)) ;


            return $res;

        }


function OLDCRUFTYthanksNeededPickList(&$co)
        {

            return ""; //XXX TEMP!

            // this is pseudo-templating, using htmltable

                $tab->addRow(
                    array(
                        'Actions'),
                    'class="tableheaders"', 'TH');

                //$co->queryFromFile('recover_thank_yous.sql');

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

function &findThanksNeeded(&$co)
        {
            $co2 =& new CoopView(&$co->page, $co->table, &$co);
            $co2->schoolYearChooser(); // to get the values
            $co2->obj->fb_forceNoChooser = 1;

            $co2->obj->fetchTemplate(&$co2);

            $co2->queryFromFile('thankyouneeded.sql');

            $co2->obj->preDefOrder = array('address' , 'dear' ,
                                          'recipient', 'items', 
                                          'value_received',
                                          'salesperson');

            $co2->obj->fb_recordActions = array();// clear them out!
            
            return $co2;
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
            
            $co->obj->preDefOrder = array( 'recipient', 'items', 'salesperson',
                                       'date_sent', 'method', 'family_id');

            $co2 =& $this->findThanksNeeded(&$co);

            return  
                '<h3>The following thank you notes need to be sent:</h3>'.
                $co2->simpleTable(false, true) .
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

            //NOTE! i sneak the schoolyear in here too!
            $co->obj->query(
                sprintf(
                    'set @school_year := "%s", @ticket_price := %d, 
@ad_text := "%s", @ticket_text := "%s", @cash_text := "%s" ',
                    $co->getChosenSchoolYear(),
                    COOP_SPRINGFEST_TICKET_PRICE, //XXX NOT GLOBAL!
                    $this->_thank_you_template->obj->ad,
                    $this->_thank_you_template->obj->ticket,
                    $this->_thank_you_template->obj->cash
                    ));

        }





    function webFormatGroupConcat(&$co, $val, $key)
        {
            // takes a field with items delimited by \n and by a leading ,
            // and makes them pretty for the web with a chr(183) delimiter
            return  preg_replace("/\n,/",'<br>',$val); 
        }

}
