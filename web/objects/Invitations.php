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


    var $fb_requiredFields = array('lead_id', 
                                   'relation', 'school_year');

	var $fb_fieldLabels = array (
		'lead_id' => 'Contact',
		'school_year' => 'School Year',
		'family_id' => 'Invited by Family',
		'relation' => 'Relation to Inviting Family',
		'label_printed' => 'Mailing Label Printed On'
		);

    /// XXX temporary hack, until i make coopform check N
    var $fb_searchSelects = array('lead_id');




    function fb_display_view(&$co)
        {

            $leads =  new CoopObject(&$co->page, 'leads', &$co);
            
            $this->orderBy('last_name, first_name, company');
            
            $co->protectedJoin($leads, 'left');
            

            // my nice little label preview
            $co->obj->selectAdd(
"concat_ws('\n'
,concat_ws(' ' , salutation, first_name, last_name)
,if(length(title)>0, title, null)
,if(length(company)>0, company, null)
,if(length(address1)>0, address1, null)
,if(length(address2)>0, address2, null)
,concat_ws(' ', concat(city, ', ', state), zip, if(country != 'USA', country, ''))
) as label_like");
            
            
            // NASTY!
            $old=array_reverse(array_reverse($this->fb_fieldLabels)) ;
            $this->fb_fieldLabels = array();
            $this->fb_fieldLabels['label_like'] = 'Address Label Preview';
            foreach($old as $k=>$v){
                if($k != 'lead_id'){
                    $this->fb_fieldLabels[$k] = $v;
                }
            }

            // only relevant for the big scary list
            $ap = "";
            if($co->isPermittedField() >= ACCESS_VIEW){
                $ap = $co->alphaPager('last_name', 'leads');
            }
            return $ap . $co->simpleTable() .$ap;
        }



    function fb_display_alert(&$co)
        {


            list($ok, $res) = $this->_alert_or_status(&$co);
            
            if($ok){
                return '';
            }
            
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


    function fb_display_summary(&$co)
        {


            list($ok, $res) = $this->_alert_or_status(&$co);

            // NOTE: i can't include the summary here because schoolyearchooser
            // hack
            if($co->isPermittedField() >= ACCESS_VIEW){
                $co->schoolYearChooser();
                $res .= $co->searchForm->toHTML();
                $res .= showRawQuery("Invitation  Counts", 
                                 sprintf('select relation, 
			sum(if(invitations.family_id>0,0,1)) as Alumni_List ,
			sum(if(invitations.family_id>0,1,0)) as Family_Supplied ,
			count(distinct(lead_id)) as Total 
		from invitations 
		where 
			  invitations.school_year = "%s" 
   		group by relation 
		order by total desc',
                                         $co->getChosenSchoolYear()));
                return $res;
            }
            if($ok){
                return $res;
            }

        }


    function _alert_or_status(&$co)
        {
            $res = '';

            $fid = $co->page->userStruct['family_id'];

            if(!$fid){
                return array(true, ''); // give up, it's a teacher
            }



            // this code ought to be taken out and shot
            $cv = new CoopObject(&$co->page, 'families_income_join', $nothing);
            $cv->obj->family_id = $fid;
            $inc = new CoopObject(&$co->page, 'income', $nothing);
            $inc->obj->school_year = $co->page->currentSchoolYear;
            $inc->obj->account_number = COOP_NAMES_FORFEIT_FEE; 
            $cv->protectedJoin($inc);
            $found = $cv->obj->find(true);
            
            // TODO: check amount is correct. duh.
            if($found){  
                $cv->obj->getLinks();
                return array(true, 
                             sprintf("Congratulations! 
						You have paid your forfeit fee of $%0.2f . 
						You don't need to enter any names this year.", 
                                    $cv->obj->_income_id->payment_amount));
            }

			// check for indulgences
            $cv = new CoopObject(&$co->page, 'nag_indulgences', $nothing);
            $cv->obj->family_id = $fid;
            $cv->obj->school_year = $co->page->currentSchoolYear;
            $cv->obj->whereAdd('(indulgence_type = "Invitations" or indulgence_type = "Everything")');
            $found = $cv->obj->find(true);
		
//		confessObj($cv);
            if($found){  
                $cv->obj->getLinks();
                return array(
                    true,
                    sprintf("You were granted a special Indulgence on %s (%s).
						You don't need to enter any names this year.", 
                            sql_to_human_date($cv->obj->granted_date), 
                            $cv->obj->note));
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
                return array(true, 
                             sprintf("Congratulations! You invited %d person%s.  
				You're welcome to enter more below if you wish.", 
                                     $count, 
                                     $count == 1 ? '' : 's'));
            }


            $res .= sprintf("You have invited %d person%s thus far. 
				You must enter %d more before %s.",
                            $count, 
                            $count == 1 ? "" : "s",
                            COOP_NAMES_QUANTITY_REQUIRED - $count,
                            timestamp_db_php($ev->event_date)
                );
            return array(false, $res);
        }


}
