<?php
/**
 * Table Definition for invitation_rsvps
 */
require_once 'DB/DataObject.php';

class Tickets extends CoopDBDO 
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

	var $fb_formHeaderText = "Springfest Event Reservations";

	var $fb_linkDisplayFields = array('lead_id', 'company_id', 
                                      'family_id', 'ticket_quantity', 
                                      'income_id');

	var $fb_requiredFields = array('ticket_type_id', 'school_year', 
								   'ticket_quantity');

    var $fb_shortHeader = 'Reservations';
    

	var $fb_enumFields = array('vip_flag');

    var $fb_joinPaths = array('family_id' => array('leads:invitations',
                                                   'families'));

    var $fb_defaults = array('family_id' => '',
                             'ticket_type_id' => COOP_TICKET_TYPE_PAID,
                             'vip_flag' => 'No'
                             );

//     WILL NOT WORK!! because i'm concating multiple fields
//     var $fb_pager =array('method' => 'alpha',
//                          'keyname' => 'last_name',
//                          'tablename' => 'leads');

    function fb_linkConstraints(&$co)
		{



            $leads =& new CoopObject(&$co->page, 'leads', &$co);
            $inv =& new CoopObject(&$co->page, 'invitations', &$co);
            $leads->protectedJoin($inv);

            $co->protectedJoin($leads);

            $income =& new CoopObject(&$co->page, 'income', &$co);

            $co->protectedJoin($income);

            $companies =& new CoopObject(&$co->page, 'companies', &$co);

            $co->protectedJoin($companies);

            $families =& new CoopObject(&$co->page, 'families', &$co);
            $co->protectedJoin($families);


            $co->constrainSchoolYear();

            $co->constrainFamily();


            $co->obj->orderBy('coalesce(leads.last_name, companies.company_name, families.name), income.check_date');

            $co->grouper();
		}


    // this function is magical, ugly, and weird. i hate it deeply.
    // did i mention the intensity which which i despise it?
    // i can't even blame it on PHP. this function is just shit.
	function updatePaddles(&$co)
		{

			if($co->id< 1){
				PEAR::raiseError('null or zero ticketid. bad.', 666);
				return;
			}

			// calc how many are present,  to delete or add
			$pad = $this->factory('springfest_attendees'); 
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
								$co->id),
								 array(), DB_FETCHMODE_ASSOC);
            if (DB::isError($data)) {
                die($data->getMessage());
            }

			$co->page->confessArray($data, "updatePaddles({$co->id})", 3);
			$man = $data['manual'];
			$auto = $data['automatic'];
			
			// add tickets
			$toadd = $this->ticket_quantity - ($man + $auto);
			while($toadd-- > 0){
				$pado = new CoopObject(&$co->page, 'springfest_attendees', &$top);
				$clone = $pado->obj->__clone();
				$pado->obj->ticket_id = $co->id;
				$pado->obj->entry_type  = 'Automatic';
				$pado->obj->school_year = $this->school_year;

				if($this->family_id > 0){
					// check for parents, tag them otherwise
					$par = new CoopObject(&$co->page, 'parents', &$top);
					$par->obj->family_id = $this->family_id;
					if($par->obj->find() < 1){
						PEAR::raiseError("no parents for familyid $this->family_id ??", 447);
					}
					while($par->obj->fetch()){
						$clone->parent_id = $par->obj->parent_id;
						$found = $clone->find();
						if(!$found){
							$pado->obj->parent_id = $par->obj->parent_id;
						}
					}
				}
				if($this->lead_id > 0){
					// make sure at least one has this leadid
					$clone->lead_id = $this->lead_id;
					$found = $clone->find();
					if(!$found){
						$pado->obj->lead_id = $this->lead_id;
					}
				}


				if($this->company_id > 0){
					// make sure at least one has this leadid
					$clone->company_id = $this->company_id;
					$found = $clone->find();
					if(!$found){
						$pado->obj->company_id = $this->company_id;
					}
				}


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
				$pad->ticket_id = $co->id;
				$pad->entry_type = 'Automatic';
				$pad->find();
				while($pad->fetch() && $todelete-- > 0){
					$pad->delete();
				}
			}

			return true;
		}

   function afterInsert(&$co)
        {
            $this->_updateSponsors(&$co);
            $this->updatePaddles(&$co);
        }
    
    function afterUpdate(&$co)
        {
            $this->_updateSponsors(&$co);
            $this->updatePaddles(&$co);
        }
    
    function _updateSponsors(&$co)
        {
            require_once('Sponsorship.php');
            $sp = new Sponsorship(&$co->page, $this->school_year);
            foreach(array('lead_id', 'company_id') as $idname){
                if($this->{$idname} > 0){
                    $sp->updateSponsorships($this->{$idname}, $idname);
                }
            }
        }


    function _onlyOne($vars)
        {
            // AHA! need to prependtable!
            // XXX need to get a coopobject in here somehow


            $count = 0;
            foreach(array($vars['tickets-lead_id'],
                          $vars['tickets-company_id']) 
                    as $val)
            {
                if($val > 0){
                    $count++;
                }
            }
            
            if($count > 1){
                $msg = "You can have ONLY ONE of Invitee Name, or a Company Name, or a Family Name, but two or more.";    
                $err['tickets-lead_id'] = $msg;
                $err['tickets-company_id'] = $msg;
                $err['tickets-family_id'] = $msg;
                return $err;
            }
            
            if($count < 1) {
                $msg = "You must have either an Invitee Name, or a Company Name, or a Family (for current members).";
                $err['tickets-lead_id'] = $msg;
                $err['tickets-company_id'] = $msg;
                $err['tickets-family_id'] = $msg;
                return $err;
            }
            
            return true; 				// copacetic
        }
    
    function postGenerateForm(&$form)
        {
            $form->addFormRule(array($this, '_onlyOne'));
            $el =& $form->getElement(
                $form->CoopForm->prependTable('lead_id'));
            $el->searchByID =  'RSVP Code';
                

        }


}
