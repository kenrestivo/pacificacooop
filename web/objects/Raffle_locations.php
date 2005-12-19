<?php
/**
 * Table Definition for raffle_locations
 */
require_once 'DB/DataObject.php';

class Raffle_locations extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'raffle_locations';                // table name
    var $raffle_location_id;              // int(32)  not_null primary_key unique_key auto_increment
    var $location_name;                   // string(255)  
    var $start_date;                      // date(10)  binary
    var $end_date;                        // date(10)  binary
    var $description;                     // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Raffle_locations',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array ('location_name');

    var $fb_shortHeader = 'Locations';
    
    var $fb_requiredFields = array(
        'location_name'
        );
    
    var $fb_fieldLabels = array(
        'location_name' => 'Location Name',
        'start_date' => 'Date Flyer Placed',
        'end_date' => 'Date Flyer Removed',
        'description' => 'Location Description',
        );
    
}
