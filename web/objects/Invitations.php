<?php
/**
 * Table Definition for invitations
 */
require_once 'DB/DataObject.php';

class Invitations extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'invitations';                     // table name
    var $invitation_id;                   // int(32)  not_null primary_key unique_key auto_increment
    var $lead_id;                         // int(32)  not_null
    var $school_year;                     // string(50)  
    var $family_id;                       // int(32)  
    var $relation;                        // string(8)  enum
    var $label_printed;                   // datetime(19)  binary

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Invitations',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('relation');
	
	var $fb_selectAddEmpty = array ('family_id', 'lead_id');

	var $fb_formHeaderText =  'Springfest Invitations';
	var $fb_shortHeader =  'Invitations';

    // basically ANY lead in this school year is a dupe
    var $fb_dupeIgnore = array('family_id', 'relation', 'label_printed');

	var $fb_fieldsToRender = array (
		'lead_id',
		'school_year' ,
		'family_id',
		'relation',
		'label_printed'
		);

    var $fb_requiredFields = array('family_id', 'lead_id', 
                                   'relation', 'school_year');

	var $fb_fieldLabels = array (
		'lead_id' => 'Contact',
		'school_year' => 'School Year',
		'family_id' => 'Invited by Family',
		'relation' => 'Relation to Inviting Family',
		'label_printed' => 'Mailing Label Printed On'
		);

    var $fb_searchSelects = array('lead_id');


    function fb_display_alert(&$co)
        {

            $fid =  $co->page->userStruct['family_id'];
            if(!$fid){
                return; // give up, it's a teacher
            }

            $res = '';

            // this code ought to be taken out and shot
            $cv = new CoopObject(&$co->page, 'families_income_join', $nothing);
            $cv->obj->family_id = $fid;
            $inc = new CoopObject(&$co->page, 'income', $nothing);
            $inc->obj->school_year = $co->page->currentSchoolYear;
            $inc->obj->account_number = COOP_NAMES_FORFEIT_FEE; 
            $cv->obj->joinAdd($inc->obj);
            $found = $cv->obj->find(true);
            
            //confessObj($cv);
            if($found){  
                return '';
            }
			// check for indulgences
            $cv = new CoopObject(&$co->page, 'nag_indulgences', $nothing);
            $cv->obj->family_id = $fid;
            $cv->obj->school_year = $co->page->currentSchoolYear;
            $cv->obj->whereAdd('(indulgence_type = "Invitations" or indulgence_type = "Everything")');
            $found = $cv->obj->find(true);
		
//		confessObj($cv);
            if($found){  
                return '';
            }
	
            $ev = $this->factory('calendar_events');
            $ev->event_id = COOP_NAMES_DUE_EVENT;
            $ev->school_year = $co->page->currentSchoolYear;
            if($ev->find(true) < 1){
                $co->page->yearNotSetupYet();
            }

            // count 'em!
            $inv = $this->factory($co->table);
            $inv->family_id = $fid;
            $inv->school_year = $co->page->currentSchoolYear;
            $count = $inv->find();
            
            if($count >= COOP_NAMES_QUANTITY_REQUIRED){
                return '';
            }


            $res .= sprintf("You have entered %d name%s thus far. 
				You must enter %d more before %s.",
                            $count, 
                            $count == 1 ? "" : "s",
                            COOP_NAMES_QUANTITY_REQUIRED - $count,
                            timestamp_db_php($ev->event_date)
                );
            
            
            return $res; //XXX temporary hack till i get sandbox first. 

            require_once('paypal.php');

            //PAYPAL BUTTON HERE
            $form =& new paypalForm('Springfest Invitations Forfeit Fee', 
                                    'inviteforfeit',   'MembersPage', false);
            $form->addElement('hidden', 'amount', '50.00');
            $form->addElement('hidden', 'custom', 
                              sprintf("fid%d:coa%d",
                                      $id, COOP_NAMES_FORFEIT_FEE)); 
            $form->addElement('submit', NULL, 
                              "Pay Names Fee");
            //fix elements
            $form->addElement("hidden", "cancel", $_SESSION['PHP_SELF']);
            $form->addElement("hidden", "item_number", "Family Fees");


            //XXXX do auto-processing before sending them back
            //$this->addElement("hidden", "return", $_SESSION['PHP_SELF']);
		
            $tab = new HTML_Table();
            $tab->addCol(array('<p>Or you may click here to pay a $50 fee instead of entering names:</p>'));
            $tab->addCol(array($form->toHTML()));
            
            $res .= $tab->toHTML();

            return $res;
            
        }



}
