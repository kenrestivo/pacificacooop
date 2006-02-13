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
	var $fb_linkDisplayFields = array('paddle_number');	
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

}
