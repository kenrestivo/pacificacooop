<?php
/**
 * Table Definition for invitation_rsvps
 */
require_once 'DB/DataObject.php';

class Tickets extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'tickets';                         // table name
    var $ticket_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $income_id;                       // int(32)  
    var $ticket_quantity;                 // int(5)  
    var $lead_id;                         // int(32)  
    var $school_year;                     // string(50)  
    var $family_id;                       // int(32)  
    var $ticket_type_id;                  // int(32)  
    var $vip_flag;                        // string(3)  enum
    var $company_id;                      // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Tickets',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_fieldLabels = array(
        'income_id' => "Payment Information",
		'ticket_quantity' => 'Number of Reservations',
		'lead_id' => 'Invitee- from Invitations',
		'company_id' => 'Invitee- from Solicitation',
		'family_id' => 'Family- Reservations for members',
		'school_year' => 'School Year',
		'ticket_type_id' => 'Type of Reservation',
		'vip_flag' => 'VIP?'
		);
	var $fb_fieldsToRender = array ('ticket_quantity',
									'school_year', 'ticket_type', 'income_id',
									'vip_flag','ticket_type_id'
		);
	var $fb_formHeaderText = "Springfest Event Reservations";

	var $fb_linkDisplayFields = array('lead_id', 'income_id');

	var $fb_requiredFields = array('ticket_type_id', 'ticket_quantity');

	function updatePaddles(&$page)
		{

			if($this->ticket_id < 1){
				user_error('tickets::updatepaddles ticketid <1', 
						   E_USER_NOTICE);
				return;
			}

			// calc how many are present,  to delete or add
			$pad = DB_DataObject::factory('springfest_attendees'); 
 			if (PEAR::isError($pad)){
				user_error("Tickets.php::updatePaddles(): db badness", 
						   E_USER_ERROR);
			}
			//confessObj($this, 'this');

			$db =& $this->getDatabaseConnection();

            $data =& $db->getRow(sprintf("select school_year, ticket_id,
						sum(if(entry_type='Automatic',1,0)) as automatic, 
						sum(if(entry_type='Manual',1,0)) as manual
						from springfest_attendees
						where ticket_id = %d group by ticket_id", 
								$this->ticket_id),
								 array(), DB_FETCHMODE_ASSOC);
            if (DB::isError($data)) {
                die($data->getMessage());
            }

			//confessArray($data, 'data');
			$man = $data['manual'];
			$auto = $data['automatic'];
			
			print "ticket $this->ticket_id man $man auto $auto<br>";
			// add tickets
			$toadd = $this->ticket_quantity - ($man + $auto);
			while($toadd-- > 0){
				$pado = new CoopObject(&$page, 'springfest_attendees', &$top);
				$pado->obj->ticket_id = $this->ticket_id;
				$pado->obj->entry_type  = 'Automatic';
				$pado->obj->paddle_number = $pado->getCounter('paddle_number',
															 $this->school_year);
				$pado->obj->school_year = $this->school_year;
				$pado->obj->insert();
			}

			// find out how many paddles can be deleted
			// if there aren't enough automatics to delete, return error
			$todelete = ($man + $auto) - $this->ticket_quantity;
			if($todelete > 0 && $todelete > $auto){
				user_error("tickets:updateapaddles: there are only $auto automatic entries, but you need to delete $todelete ones", E_USER_NOTICE);
				return false;
			}

			if($todelete > 0){
				//if  ticket q < found, delete the remaining automatics
				$pad = DB_DataObject::factory('springfest_attendees'); 
				if (PEAR::isError($pad)){
					user_error("Tickets.php::updatePaddles(): db badness", 
							   E_USER_ERROR);
				}
				$pad->ticket_id = $this->ticket_id;
				$pad->entry_type = 'Automatic';
				$pad->find();
				while($pad->fetch() && $todelete-- > 0){
					$pad->delete();
				}
			}

			return true;
		}


}
