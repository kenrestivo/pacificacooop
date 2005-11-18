<?php
/**
 * Table Definition for families_income_join
 */
require_once 'DB/DataObject.php';

class Families_income_join extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'families_income_join';            // table name
    var $families_income_join_id;         // int(32)  not_null primary_key unique_key auto_increment
    var $income_id;                       // int(32)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Families_income_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_fieldLabels = array ('income_id' => "Check", 
								 'family_id' => "Co-op Family");

	var $fb_formHeaderText =  'Family Fees';

	var $fb_requiredFields = array('income_id', 'family_id');

	var $fb_linkDisplayFields = array('income_id', 'family_id');

    var $fb_shortHeader = 'Family Fees';

    var $fb_joinPaths = array('school_year' => 'income');

	function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'income', &$co);
            $co->constrainSchoolYear();
            $co->constrainFamily();
            $co->protectedJoin($auc);
            $co->orderByLinkDisplay();
            $co->grouper();
		}



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
            $form =& new paypalForm('Springfest Food/Raffle Fee', 
                                    'quiltfee',   'MembersPage', false);
            $form->addElement('hidden', 'amount', COOP_FOOD_VALUE_REQUIRED);
            $form->addElement('hidden', 'custom', 
                              sprintf("fid%d:coa%d",
                                      $id, COOP_FOOD_FEE)); 
            $form->addElement('submit', NULL, 
                              "Pay Auction Fee");
            //fix elements
            $form->addElement("hidden", "cancel", $_SESSION['PHP_SELF']);

            // XXX why is this the username?
            $form->addElement("hidden", "item_number", 
                              $co->page->userStruct['username']);


            //XXXX do auto-processing before sending them back
            //$this->addElement("hidden", "return", $_SESSION['PHP_SELF']);
		
            //TODO: use css
            $tab = new HTML_Table();
            $tab->addCol(
                array(
                    sprintf('<p>Each family must pay a $%0.2f food/raffle fee. Click here to pay it online via your credit card:</p>',
                            COOP_FOOD_VALUE_REQUIRED)));
            $tab->addCol(array($form->toHTML()));
            
            $res .= $tab->toHTML();
            
            return $res;
            
        }


    function fb_display_summary(&$co)
        {
            list($ok, $res) = $this->_alert_or_status(&$co);

            // NOTE: i can't include the summary here because schoolyearchooser

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



			// check for indulgences
            $cv = new CoopObject(&$co->page, 'nag_indulgences', $nothing);
            $cv->obj->family_id = $fid;
            $cv->obj->school_year = $co->page->currentSchoolYear;
            $cv->obj->whereAdd('(indulgence_type = "Quilt Fee" or indulgence_type = "Everything")');
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
	



            // this code ought to be taken out and shot
            $cv = new CoopObject(&$co->page, 'families_income_join', $nothing);
            $cv->obj->family_id = $fid;
            $inc = new CoopObject(&$co->page, 'income', $nothing);
            $inc->obj->school_year = $co->page->currentSchoolYear;
            $inc->obj->account_number = COOP_FOOD_FEE; 
            $cv->protectedJoin($inc);
            $found = $cv->obj->find(true);
            
            // TODO: check amount is correct. duh.
            if($found){  
                $cv->obj->getLinks();
                return array(true, 
                             sprintf("Congratulations! 
						You have paid your food/raffle fee of $%0.2f.", 
                                    $cv->obj->_income_id->payment_amount));
            }



            $ev = $this->factory('calendar_events');
            $ev->event_id = COOP_FOOD_FEE_DUE_EVENT;
            $ev->school_year = $co->page->currentSchoolYear;
            if($ev->find(true) < 1){
                $co->page->yearNotSetupYet();
            }


            $res .= sprintf(' You must pay your $%0.02f food/raffle fee before %s.',
                            COOP_FOOD_VALUE_REQUIRED ,
                            timestamp_db_php($ev->event_date)
                );
            return array(false, $res);
        }




}
