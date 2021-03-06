<?php
/**
 * Table Definition for families
 */
require_once 'COOP/DBDO.php';

class Families extends CoopDBDO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'families';                        // table name
    var $family_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $name;                            // string(255)  
    var $phone;                           // string(20)  
    var $address1;                        // string(255)  
    var $email;                           // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Families',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('name');
	var $fb_fieldLabels = array (
		'name' => 'Family Name',
		'phone' => 'Phone Number',
		'address1' => 'Address',

		'email' => 'Email Address'
		);
	var $fb_requiredFields = array ('name', 'phone', 'address1');
	//var $fb_crossLinks = array(array('table' => 'families_income_join',
	//'fromField' => 'family_id', 'toField' => 'income_id'
	var $fb_linkNewValue = 1;
    var $fb_formHeaderText = "Co-Op Member Families";
    var $fb_shortHeader = "Families";
    var $fb_joinPaths = array('school_year' => 'kids:enrollment');

    // NOTE special case here: everythign must be dupignore to guarantee
    // that name is unique: any dupe's by name will generate an error
    var $fb_dupeIgnore = array('phone', 'address1', 'email');

    var $fb_extraDetails = array('parents:enhancement_hours',
                                 'families_income_join:income',
                                 'auction_items_families_join:auction_donation_items',
                                 'kids:enrollment');



    function postGenerateForm(&$form)
        {
            if($form->elementExists($form->CoopForm->prependTable('email'))){
                $form->addRule($form->CoopForm->prependTable('email'), 
                               'Email address must be valid', 'email', true);
            }
        }


 	function fb_linkConstraints(&$co)
		{

            // HACK! this is presuming VIEW, but in popup it could be EDIT
             // the betsy hack
            if(!($co->isPopup && $co->perms[null]['year'] >= ACCESS_EDIT))
            {


                $enr =& new CoopObject(&$co->page, 'enrollment', &$co);
                $kid =& new CoopObject(&$co->page, 'kids', &$co);

                $kid->protectedJoin($enr);
                $co->protectedJoin($kid);

                $co->constrainSchoolYear();

                //$co->constrainFamily(): // no point in this?

                // TODO: add teh drop day part of the query!!
                // look at enrollment totals. this is a fairly invasive change

                // XXX THIS HAS AN AWFUL BUG!
                // it will grab dropout dates from OLD YEARS

            
                // NEED THIS HACK,
                // otherwise perms calculates based on the LAST of the years
                $co->obj->selectAdd('max(school_year) as school_year');
            }

            $co->obj->orderBy();
            $co->obj->orderBy('families.name');
            $co->grouper();
            

            //$co->debugWrap(4);

 		}

    function fb_display_view(&$co)
        {
            
            $co->schoolYearChooser(); // so that getchosenschoolyear comes up!
            //don't whack the unenrolled on allyears
            if($co->getChosenSchoolYear() != '%'){
                $this->whereAdd(
                    sprintf('(dropout_date is null or dropout_date < "2000-01-01" or dropout_date > "%s")', 
                            date('Y-m-d')));
            }
            $res = $this->familyTotals(&$co);
            array_push($co->obj->preDefOrder, 'am_pm_session');
            $co->obj->fb_fieldLabels['am_pm_session'] = 'Session';
            $res .= $co->simpleTable(true,true);
            return $res;

        }

    // this is the summary we need for jefferson, for calculating sandy's salary
    function familyTotals(&$co)
        {
            $sy = $co->getChosenSchoolYear();
            $res = "<div>Enrolled Families for {$sy}:<br>";
            $famtot = 0;
            foreach (array('AM', 'PM') as $sess){
                // NOTE: not $co->table, since you could be called elsewhere!
                $co2 =& new CoopView($co->page, 'families', $none);
                $co2->schoolYearChooser(); // to get the chosen above
                $co2->forceNoChooser = 1;
                $co2->obj->whereAdd("am_pm_session = '$sess'");
                $co2->obj->whereAdd(
                    sprintf('(dropout_date is null or dropout_date < "2000-01-01" or dropout_date > "%s")', 
                            date('Y-m-d')));
                $co2->find(true); // MY find, which does linkconstraints
                $famtot += $co2->obj->N;
                $res .= "$sess: {$co2->obj->N}<br>" ;
            }
            $res .= "Total: $famtot</div>";
            return $res;
        }


    function afterInsert(&$co)
        {

            //lawwdy help you if you change column names in families or users!

            // they are users too
            $user = $this->factory('users');
            $user->family_id = $co->id;
            $user->name = $this->name . ' Family';
            $user->insert();
            $uid = $co->lastInsertID();

            //any new family you add? they are members. dammit.
            $ugj = $this->factory('users_groups_join');
            $ugj->user_id = $uid;
            $ugj->group_id = 1; // MEMBERS
            $ugj->insert();

            // TODO: user defaults... like subscriptions

            
        }

    // NOTE i don't override delete because my confirmdelete link checks should find that
    
}