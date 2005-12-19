<?php
/**
 * Table Definition for flyer_deliveries
 */
require_once 'DB/DataObject.php';

class Flyer_deliveries extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'flyer_deliveries';                // table name
    var $flyer_delivery_id;               // int(32)  not_null primary_key unique_key auto_increment
    var $flyer_type;                      // string(255)  
    var $delivered_date;                  // date(10)  binary
    var $family_id;                       // int(32)  
    var $company_id;                      // int(32)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Flyer_deliveries',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


    var $fb_formHeaderText = 'Springfest Flyer Deliveries';
    var $fb_shortHeader = 'Deliveries';
    
    var $fb_requiredFields = array(
        'company_id',
        'delivered_date',
        'family_id',
        'school_year'
        );
    
	var $fb_fieldLabels = array (
		'company_id' => 'Company Name'
		'flyer_type' => 'Flyer Type',
		'delivered_date' => 'Delivered On',
		'family_id' => 'Delivering Family',
		'school_year' => 'School Year',
		);



}
