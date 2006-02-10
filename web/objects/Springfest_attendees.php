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
		'paddle_number' => 'Paddle Number',
		'ticket_id' => 'Reservation Holder',
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
	var $fb_fieldsToRender = array ('paddle_number' , 'ticket_id' , 
									'lead_id' , 'company_id', 'parent_id',
									'attended'
		);
	
    var $fb_shortHeader = 'Paddles';
    
    var $fb_requiredFields = array(
        'springfest_attendee_id',
        'school_year'
        );

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

			//user_error("paddle counter $id", E_USER_NOTICE);
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


}
