<?php
/**
 * Table Definition for raffle_income_join
 */
require_once 'DB/DataObject.php';

class Raffle_income_join extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'raffle_income_join';              // table name
    var $raffle_income_join_id;           // int(32)  not_null primary_key unique_key auto_increment
    var $raffle_location_id;              // int(32)  
    var $income_id;                       // int(32)  
    var $family_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Raffle_income_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('raffle_location_id', 'income_id');

	var $fb_fieldLabels = array ('income_id' => 'Payment Information',
                                 'raffle_location_id' => 'Raffle Location',
                                 'family_id' => 'Family Handling This');

	var $fb_formHeaderText =  'Springfest Raffle Sales';

	var $fb_requiredFields = array('raffle_location_id',
                                   'family_id',
                                   'income_id');


    var $fb_shortHeader = 'Raffle Sales';
    var $preDefOrder = array('raffle_location_id', 'income_id', 'family_id');

    var $fb_joinPaths = array('school_year' => 'income');


    function fb_linkConstraints(&$co)
		{
            $auc =& new CoopObject(&$co->page, 'income', 
                                   &$co);
            $auc->constrainSchoolYear();
            $co->protectedJoin($auc);
            $companies =& new CoopObject(&$co->page, 'raffle_locations', 
                                   &$co);
            $co->protectedJoin($companies);

           // TODO: somehow make orderbylinkdisplay() recursive
            $co->obj->orderBy('raffle_locations.location_name,  income.check_date');
            $co->grouper();
		}



}
