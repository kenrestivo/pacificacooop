<?php
/**
 * Table Definition for springfest_attendees
 */
require_once 'DB/DataObject.php';

class Springfest_attendees extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'springfest_attendees';            // table name
    var $springfest_attendee_id;          // int(32)  not_null primary_key unique_key auto_increment
    var $paddle_number;                   // int(32)  
    var $ticket_id;                       // int(32)  
    var $lead_id;                         // int(32)  
    var $company_id;                      // int(32)  
    var $parent_id;                       // int(32)  
    var $temp_name;                       // string(255)  
    var $school_year;                     // string(50)  
    var $entry_type;                      // string(9)  enum
    var $attended;                        // string(7)  enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Springfest_attendees',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	// AACK! there is no sane way to linkdisplay this! until i merge people
	// really, this could be a lead, a parent, a ticket, a company. bah.
	var $fb_linkDisplayFields = array('paddle_number', 'lead_id', 
                                      'company_id', 'parent_id');	
	var $fb_fieldLabels = array (
        'springfest_attendee_id' => 'Attendee ID (system internal only)',
		'paddle_number' => 'Paddle Number',
		'ticket_id' => 'Reservation/Ticket',
		'lead_id' => 'Invitee',
		'company_id' => 'Company',
		'parent_id' => 'Parent',
		'entry_type' => 'Entry Control',
		'temp_name' => 'HACK temporary name',
		'attended' => 'Attended',
		'school_year' => 'School Year'
		);
	var $fb_enumFields = array ('entry_type', 'attended');
	var $fb_selectAddEmpty = array ('lead_id', 'parent_id', 'company_id',
									'ticket_id');
	var $fb_formHeaderText =  'Springfest Attendees';
	
    var $fb_shortHeader = 'Paddles';

    var $fb_fieldsToUnRender = array('temp_name');
    
    var $fb_requiredFields = array(
        'school_year',
        'ticket_id'
        );

    var $fb_joinPaths = array('family_id' => array('parents',
                                                   'tickets',
                                                   'companies'));


    var $fb_defaults = array('entry_type' => 'Manual');

    var $fb_forceCustomSelect = 1;


    function fb_linkConstraints(&$co)
		{

            $leads =& new CoopObject(&$co->page, 'leads', &$co);
            $co->protectedJoin($leads);


            $companies =& new CoopObject(&$co->page, 'companies', &$co);

            $co->protectedJoin($companies);


            $parents =& new CoopObject(&$co->page, 'parents', &$co);
            $co->protectedJoin($parents);

            $tickets =& new CoopObject(&$co->page, 'tickets', &$co);
            $co->protectedJoin($tickets);

            /// AACHHGH! i hate DBDO. it doesn't know which id to
            /// use if you have more than one.
            $co->obj->selectAdd('companies.company_id as company_id, springfest_attendees.lead_id as lead_id');

            $co->constrainSchoolYear();

            $co->constrainFamily();

            $co->obj->orderBy('coalesce(leads.last_name, companies.company_name, parents.last_name), springfest_attendees.ticket_id');

            $co->grouper();
		}



	//the paddle number
	function insert()
		{
            // by default, the school year here,
            // but don't i want getchosenschoolyear instead?
            // i'd need to get the coopobject/cooppage in here then
			if(!$this->school_year){
				$this->school_year  = findSchoolYear();
			}
			$clone = $this->__clone();
            
            // check for no counter there for this year!!
            $this->startPaddleForThisYear(&$co);

			$clone->query(sprintf("update counters set 
						counter = last_insert_id(counter+1) 
				where column_name='paddle_number' 
						and school_year = '%s'", 
								  $this->school_year));
            $db =& $clone->getDatabaseConnection();

            $id =& $db->getOne('select last_insert_id()');
            if (DB::isError($data)) {
                PEAR::raiseError($data->getMessage(), 666);
            }

            //NOTE: don't be a schmuck and try to enter one
            //the system will override it right here:
			$this->paddle_number = $id;

			parent::insert();
		}


    function startPaddleForThisYear(&$co)
        {
            $db =& $this->getDatabaseConnection();

            $count =& $db->getOne(
                sprintf('select count(counter_id) from counters where column_name = "paddle_number" and school_year = "%s"', 
                        $this->school_year));
            if (DB::isError($count)) {
                PEAR::raiseError($count->getMessage(), 666);
            }
            
            if($count < 1){
                $res =& $db->getOne(
                    sprintf('insert into counters set column_name = "paddle_number", school_year = "%s", counter = 0', 
                            $this->school_year));
                if (DB::isError($res)) {
                    PEAR::raiseError($res->getMessage(), 666);
                }
            }

        }

    function preGenerateForm(&$form)
        {

            // XXX XXX nasty hack around some stupid bug in coopform
            //$form->CoopForm->page->confessArray($this, 'wtf', 2);

            if($this->lead_id < 1 && $this->company_id < 1 
               && $this->parent_id < 1)
            {
                $this->school_year = $form->CoopForm->getChosenSchoolYear();
            }
        }

    function postGenerateForm(&$form)
        {
            $form->addFormRule(array($this, '_onlyOne'));
            $el =& $form->getElement($form->CoopForm->prependTable('entry_type'));
            $el->setValue('Manual');


            // XXX i should really do this in perms,
            // but perms are broken
            $form->freeze('springfest_attendees-paddle_number');

        }

 
    function _onlyOne($vars)
        {

            // AHA! need to prependtable!
            // XXX need to get a coopobject in here somehow

            $count = 0;
            foreach(array($vars['springfest_attendees-lead_id'],
                          $vars['springfest_attendees-company_id'],
                          $vars['springfest_attendees-parent_id'])
                    as $val)
            {
                if($val > 0){
                    $count++;
                }
            }

            if($count > 1) {
                $msg = "You can have ONLY ONE of Invitee Name, or a Company Name, or Parent (for current members), but two or more.";    
                $err['springfest_attendees-lead_id'] = $msg;
                $err['springfest_attendees-company_id'] = $msg;
                $err['springfest_attendees-parent_id'] = $msg;
                return $err;
            }
            
            if($count < 1){
                $msg = "You must have either an Invitee Name, or a Company Name, or Parent (for current members).";
                $err['springfest_attendees-lead_id'] = $msg;
                $err['springfest_attendees-company_id'] = $msg;
                $err['springfest_attendees-parent_id'] = $msg;
                return $err;
            }
            
            return true; 				// copacetic
        }


function prepare_view(&$co)
        {
            $co->schoolYearChooser();
            $sy = $co->getChosenSchoolYear();

            //// ADD SHOW BUTTONS
            $whereadd = array();
            foreach (
                array(
                    'members' => array(
                        'title' => 'Members (parents)',
                        'whereadd' => 'ticket_summary.family_id > 0',
                        'default' => 1), 
                    'solicitation' => array(
                        'title' => 'Solicitation Contacts',
                        'whereadd' => 'ticket_summary.company_id > 0',
                        'default' => 1), 
                    'invitees' => array(
                        'title' => 'Invitation Contacts',
                        'whereadd' => 'ticket_summary.lead_id > 0',
                        'default' => 1),
                    'blanks' => array(
                        'title' => 'Blanks (for purchasing tickets at the door)',
                        'whereadd' => 'ticket_summary.ticket_id is null',
                        'default' => 0)) 
                as $show => $details)
            {
                $button =& $co->searchForm->addElement(
                    'advcheckbox', 
                    'show_'.$show, 
                    $show == 'members'?'Show':NULL,
                    $details['title']);
                
                
                $co->searchForm->setDefaults(
                    // NOTE! isset not empty! preserve nulls!
                    array('show_'.$show => 
                          isset($co->page->vars['last']['show_'.$show]) ? $co->page->vars['last']['show_'.$show] : $details['default']));
            
            
                $showresults[$show] = $button->getValue();
                $co->page->vars['last']['show_'.$show] = $showresults[$show];


                // not the DBDO style of whereadd, my own weird hybrid
                if($co->page->vars['last']['show_'.$show] > 0){
                    $whereadd[] = $details['whereadd'];
                }
            }
            $where = '1 = 0';
            if(count($whereadd) > 0 ){
                $where = implode(' or ', $whereadd);
            }


                


            $co->showChooser = 1;
            $co->searchForm->addElement('submit', 'savebutton', 'Change');
            
            

            $co->obj->query(
                "
select springfest_attendees.springfest_attendee_id, 
springfest_attendees.paddle_number, ticket_summary.vip_flag,
coalesce(leads.first_name, companies.first_name, parents.first_name) 
        as first_name,
coalesce(leads.last_name, companies.last_name, parents.last_name) as last_name,
coalesce(leads.company, companies.company_name) as company_name,
coalesce(leads.address1, companies.address1, families.address1) as address1,
coalesce(leads.address2, companies.address2) as address2,
coalesce(leads.city, companies.city) as city,
coalesce(leads.state, companies.state) as state,
coalesce(leads.zip, companies.zip) as zip,
coalesce(leads.phone, companies.phone, families.phone) as phone,
coalesce(leads.email_address, companies.email_address, families.email) as email_address,
ticket_summary.ticket_purchaser,
truncate(income.payment_amount / ticket_summary.ticket_quantity,2) as payment_amount,
springfest_attendees.attended, 
coalesce(parents.family_id, ticket_summary.family_id, companies.family_id) as family_id, springfest_attendees.school_year
from springfest_attendees
left join leads on springfest_attendees.lead_id = leads.lead_id
left join companies on springfest_attendees.company_id = companies.company_id
left join parents on springfest_attendees.parent_id = parents.parent_id
left join families on parents.family_id = families.family_id
left join
(select tickets.ticket_id, tickets.vip_flag, tickets.income_id, 
    tickets.school_year, tickets.ticket_quantity,
    concat_ws('\n', concat_ws(' ',coalesce(leads.first_name, companies.first_name) ,
    coalesce(leads.last_name, companies.last_name, 
        concat(families.name, ' Family'))),
    coalesce(leads.company, companies.company_name),
    concat_ws(' ', 
      coalesce(leads.address1, companies.address1, families.address1),
    coalesce(leads.address2, companies.address2)),
    concat_ws(' ', coalesce(leads.city, companies.city),
    coalesce(leads.state, companies.state),
    coalesce(leads.zip, companies.zip)),
    coalesce(leads.phone, companies.phone, families.phone),
    coalesce(leads.email_address, companies.email_address, families.email)) 
        as ticket_purchaser,
    coalesce(leads.first_name, companies.first_name) as first,
    coalesce(leads.last_name, companies.last_name, 
        concat(families.name, ' Family')) as last, tickets.family_id, tickets.company_id, tickets.lead_id
    from tickets
    left join leads on tickets.lead_id = leads.lead_id
    left join companies on tickets.company_id = companies.company_id
    left join families on tickets.family_id = families.family_id
) as ticket_summary 
    on ticket_summary.ticket_id = springfest_attendees.ticket_id
left join income on ticket_summary.income_id = income.income_id
where springfest_attendees.school_year = '$sy' and ($where)
order by 
coalesce(leads.last_name, companies.last_name, parents.last_name, ticket_summary.last),
coalesce(leads.first_name, companies.first_name, parents.first_name, ticket_summary.first)
");

            // i only want these
            $co->obj->fb_fieldsToRender= array('paddle_number', 
                                               'ticket_purchaser',
                                               'first_name',
                                               'last_name', 'company_name',
                                               'address1', 'address2',
                                               'city', 'state','zip', 
                                               'phone', 'email_address', 
                                               'vip_flag',
                                               'attended', 'payment_amount'
                );

            $co->obj->fb_fieldLabels= array('paddle_number' => 'Paddle Number', 
                                            'last_name' => "Last Name",  
                                            'first_name' => "First Name",
                                            'ticket_purchaser' => 
                                            'Reservation Purchased By (or granted to)',
                                            'company_name' =>"Company",
                                            'address1' => "Address", 
                                            'address2' => "Address2",
                                            'city' => "City", 
                                            'state' => "State",
                                            'zip' => "Zip Code", 
                                            'phone' => "Phone", 
                                            'vip_flag' => "VIP?",
                                            'attended' => "Attended",
                                            'payment_amount' => 'Paid (per person)'
                );

        }


    function fb_display_view(&$co)
        {
            $this->prepare_view(&$co);
            return $co->simpleTable(false, true);

        }

}
