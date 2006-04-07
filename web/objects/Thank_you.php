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
		'family_id' => 'Printed/Sent By');


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
        'items' => 'formatGroupConcat',
        'value_received' => 'formatGroupConcat');

    var $fb_bodyFormat = 'html';

//     function fb_linkConstraints(&$co)
// 		{
//             $co->buildConstraintsFromJoinPaths();
            
//         }

    var $fb_substitutionVars = array(
        'DATE' => 'date',
        'DEAR' => 'dear',
        'NAME' => 'name',
        'ITERATION' => 'iteration',
        'ORDINAL' => 'ordinal',
        'YEAR' => 'year',
        'YEARS' => 'years',
        'FROM' => 'from',
        'EMAIL' => 'email',
        'ADDRESS' => 'address',
        'ITEMS' => 'items',
        'VALUERECEIVED' => 'value_received');

    // well, this sucks
    var $main_body = 'fetchTemplate() or buildBody() are broken';


// the calllback
function recordButtons(&$co, $par, $wrap)
        {
            $res = "";

            if($co->obj->{$co->pk} > 0){
                // we have an existing thank you to recover
                $res .= $co->page->selfURL(
					array('value' => 
						  'Reprint/View',
						  'base' =>'print_popup.php', 
						  'inside' => array(
                              'thing' => 'letters',
                              'set' => 'printed',
                              $co->prependTable($co->pk) => 
                              $co->obj->{$co->pk}),
						  'popup' => true,
						  'par' => $par)) ;

                $res .= $co->innerRecordButtons($par, $wrap);
            } else {
                // we have new thank you's, not yet sent

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


            }
            
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
						  'par' => false)) . 
                '&nbsp;(NOTE: may take several minutes to run)<br />'.
                $co2->simpleTable(false, true) ;
        }

function findThanksNeeded(&$co)
        {
            $co->schoolYearChooser(); // to get the values
            $co->obj->fb_forceNoChooser = 1;

            $co->obj->fetchTemplate(&$co);

            $co->queryFromFile('thankyouneeded.sql');

            $co->obj->preDefOrder = array('address' , 'dear' ,
                                          'recipient', 'items', 
                                          'value_received',
                                          'salesperson');

            $co->obj->fb_recordActions = array();// clear them out!
            
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

            $co2 =& new CoopView(&$co->page, $co->table, &$co);
            $co2->obj->findThanksNeeded(&$co2);

            return  javaPopup() .
                '<h3>The following thank you notes need to be sent:</h3>'.
                $co2->simpleTable(false, true) .
                '<h3>Thank you notes below have already been sent:</h3>'.
                $co->simpleTable(false,true);

        }



function fetchTemplate(&$co)
        {
            $co->obj->_thank_you_template = new CoopObject(&$co->page, 
                                 'thank_you_templates', &$co);
            $co->obj->_thank_you_template->obj->whereAdd(
                sprintf('school_year = "%s"', 
                        $co->getChosenSchoolYear()));
            $co->obj->_thank_you_template->obj->find(true);

            // some are not specific to each record
            $co->obj->globalBodyVars(&$co);

            $co->obj->buildBody(&$co);
            
            //NOTE! i sneak the schoolyear in here too!
            $co->obj->query(
                sprintf(
                    'set @school_year := "%s", @ticket_price := %d, 
@ad_text := "%s", @ticket_text := "%s", @cash_text := "%s" ',
                    $co->getChosenSchoolYear(),
                    COOP_SPRINGFEST_TICKET_PRICE, //XXX NOT GLOBAL!
                    $co->obj->_thank_you_template->obj->ad,
                    $co->obj->_thank_you_template->obj->ticket,
                    $co->obj->_thank_you_template->obj->cash
                    ));
        }
    

    // we will PUMP, you up!
    // converts the template body into TAL format, and inserts it into the obj
    // NOTE: i'm really marrying myself to TAL here. that might bite later
    function buildBody(&$co)
        {
            foreach($co->obj->fb_substitutionVars as $templatekey => $objkey){
				$subst[sprintf('[:%s:]', $templatekey)] = 
                    sprintf(
                        '<span tal:content="structure note/%s"></span>', 
                        $objkey);
            }

            $co->obj->main_body = 
                str_replace(
                    array_keys($subst), 
                    array_values($subst), 
                    $co->obj->_thank_you_template->obj->main_body);

            //confessObj($co->obj, 'wtf');
        }


    // the body vars which are generic to all records for this template
    function globalBodyVars(&$co)
        {
			// set defaults if empty: date, schoolyear, etc
            $tmp = explode('-', $co->getChosenSchoolYear());
            $co->obj->year = $tmp[1];

            $co->obj->years = $co->obj->year - COOP_FOUNDED;
			
            $co->obj->iteration = $co->obj->year - COOP_FIRST_SPRINGFEST;
            
            $co->obj->ordinal = makeOrdinal($co->obj->iteration);

            $co->obj->date = date('l, F j, Y');

        }




    function formatGroupConcat(&$co, $val, $key)
        {
            // takes a field with items delimited by \n and by a leading ,
            // and makes them pretty for the web with a chr(183) delimiter
            $res = array();
            // note "" not '' for \n in PHP. bah.
            foreach(explode("\n", $val) as $item){
                if ($item > ""){
                    $res[] = preg_replace("/^,/",'',$item); 
                }
            }
            
            // for web formatting, maybe in already-sent letters?
            //return implode('<br>', $res);

            // for thankyouletter
            if(count($res) > 1){
                $res[count($res) - 1 ] = 'and ' . $res[count($res) - 1];
            }

            return implode(count($res) > 2 ? ', ': ' ', $res);
        }

}
