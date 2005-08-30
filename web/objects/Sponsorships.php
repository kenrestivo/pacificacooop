<?php
/**
 * Table Definition for sponsorships
 */
require_once 'DB/DataObject.php';

class Sponsorships extends DB_DataObject 
{
###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'sponsorships';                    // table name
    var $sponsorship_id;                  // int(32)  not_null primary_key unique_key auto_increment
    var $company_id;                      // int(32)  
    var $lead_id;                         // int(32)  
    var $sponsorship_type_id;             // int(32)  
    var $entry_type;                      // string(9)  enum
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sponsorships',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
###END_AUTOCODE
	var $fb_enumFields = array ('entry_type');
	var $fb_formHeaderText =  'Springfest Sponsorships';
	var $fb_linkDisplayFields = array('company_id', 'lead_id',
									  'school_year');
	var $fb_fieldLabels = array (
		'company_id' => 'Company Name',
		'lead_id' => 'Invitee Name',
		'sponsorship_type_id' => 'Sponsorship Package',
		'entry_type' => 'Entry Control',
		'school_year' => 'School Year'
		);
	
	var $fb_fieldsToRender = array (
		'company_id' ,
		'lead_id' ,
		'sponsorship_type_id', 
		'entry_type',
		'school_year' 
		);
	var $fb_requiredFields = array (
		'sponsorship_type_id', 
		'entry_type',
		'school_year' 
		);


    function fb_display_details()
        {

            $top =& $this->CoopView;      // make life sane!
            $cp =& $this->CoopView->page;

            $top->obj->{$top->pk} = $_REQUEST[$top->pk];
            $top->obj->find(true);		//  XXX aack! need this for summary
            print $top->horizTable();

	
            if($top->obj->company_id > 0){
                $comp = new CoopView(&$cp, 'companies', &$top);
                $comp->obj->{$comp->pk} = $top->obj->{$comp->pk};
                print $comp->simpleTable();
                // NOTE this is the stuff i use all below,
                // which is from solicit_company.inc
                $mi = $comp->pk;
                $cid = $comp->obj->{$comp->pk};

                foreach(array('flyer_deliveries', 
                              'solicitation_calls', 'ads') as $table)
                {
                    $view = new CoopView(&$cp, $table, &$top);

                    if(in_array('school_year', 
                                array_keys(get_object_vars($view->obj)))){
                        $view->obj->orderBy('school_year desc');
                    }
                    $view->obj->$mi = $cid;
                    print $view->simpleTable();
                }

                $co = new CoopObject(&$cp, 'companies_income_join', &$comp);
                $co->obj->$mi = $cid;
                $real = new CoopView(&$cp, 'income', &$co);
                $real->obj->orderBy('school_year desc');
                $real->obj->joinadd($co->obj);
                //$real->obj->fb_fieldsToRender[] = 'family_id';
                print $real->simpleTable();
	

                $co = new CoopObject(&$cp, 'companies_auction_join', &$comp);
                $co->obj->$mi = $cid;
                $real = new CoopView(&$cp, 'auction_donation_items', &$co);
                $real->obj->orderBy('school_year desc');
                $real->obj->joinadd($co->obj);
                print $real->simpleTable();

                $co = new CoopObject(&$cp, 'companies_in_kind_join', &$comp);
                $co->obj->$mi = $cid;
                $real = new CoopView(&$cp, 'in_kind_donations', &$co);
                $real->obj->joinadd($co->obj);
                print $real->simpleTable();

                //TODO put tickets in here!


                $inv =& new CoopObject(&$cp, 'springfest_attendees', &$comp);
                $inv->obj->$mi = $cid;
                $inc = new CoopView(&$cp, 'auction_purchases', &$comp);
                $inc->obj->joinAdd($inv->obj);
                print $inc->simpleTable();

            } // end company stuff

            /// now the lead stuff
            if($top->obj->lead_id > 0){

                $real = new CoopView(&$cp, 'leads', &$top);
                $real->obj->{$real->pk} = $top->obj->{$real->pk};
                print $real->simpleTable();
                // NOTE this is the stuff i use all below, which is from 10names.inc
                $mi = $real->pk;
                $cid = $real->obj->{$real->pk};

                $invi = new CoopView(&$cp, 'invitations', &$real);
                //print "CHECKING $table<br>";
                $invi->obj->$mi = $cid;
                $invi->obj->fb_fieldsToRender = array('relation', 'label_printed', 
                                                      'school_year', 'family_id');
                $invi->obj->orderBy('label_printed desc');
                print $invi->simpleTable();

                $view = new CoopView(&$cp, 'tickets', &$real);
                //print "CHECKING $table<br>";
                $view->obj->$mi = $cid;
                $view->obj->whereAdd('ticket_quantity > 0');
                print $view->simpleTable();

                // income, direct
                $inv =& new CoopObject(&$cp, 'leads_income_join', &$real);
                $inv->obj->$mi = $cid;
                $inc = new CoopView(&$cp, 'income', &$inv);
                $inc->obj->joinAdd($inv->obj);
                $inc->obj->orderBy('check_date desc');
                print $inc->simpleTable();

                // auction purchases
                $inv =& new CoopObject(&$cp, 'springfest_attendees', &$real);
                $inv->obj->$mi = $cid;
                $inc = new CoopView(&$cp, 'auction_purchases', &$real);
                $inc->obj->joinAdd($inv->obj);
                $inc->obj->orderBy('school_year desc');
                print $inc->simpleTable();


            } // end lead stuff

            // standard audit trail, for all details
            $aud =& new CoopView(&$cp, 'audit_trail', &$top);
            $aud->obj->table_name = $top->table;
            $aud->obj->index_id = $_REQUEST[$top->pk];
            $aud->obj->orderBy('updated desc');
            print $aud->simpleTable();

        }

    function fb_display_view()
        {
            $atd =& $this->CoopView;
            $cp =& $this->CoopView->page;
            $co =& new CoopObject(&$cp, 'sponsorship_types', &$atd);
            $atd->obj->joinAdd($co->obj);
            $atd->obj->school_year = findSchoolYear();
            $atd->obj->orderBy('sponsorship_price desc');
            $atd->obj->fb_fieldsToRender = array('company_id', 'lead_id', 
                                                 'sponsorship_type_id', 
                                                 'entry_type');
            return $atd->simpleTable();

        }

    function _onlyOne($vars)
        {
            
            $msg = "You can have an Invitee Name, or a Company Name, but not both.";
            if($vars['lead_id'] > 0 && $vars['company_id'] > 0){
                $err['lead_id'] = $msg;
                $err['company_id'] = $msg;
                return $err;
            }
            
            $msg = "You must have either an Invitee Name, or a Company Name.";
            if($vars['lead_id'] <1 && $vars['company_id'] <1){
                $err['lead_id'] = $msg;
                $err['company_id'] = $msg;
                return $err;
            }
            
            return true; 				// copacetic
        }
    
    function postGenerateForm(&$form)
        {
            $form->addFormRule(array($this, '_onlyOne'));
            $el =& $form->getElement($form->CoopForm->prependTable('entry_type'));
            $el->setValue('Manual');

        }


}
