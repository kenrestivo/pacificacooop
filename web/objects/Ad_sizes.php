<?php
/**
 * Table Definition for ad_sizes
 */
require_once 'DB/DataObject.php';

class Ad_sizes extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'ad_sizes';                        // table name
    var $ad_size_id;                      // int(32)  not_null primary_key unique_key auto_increment
    var $ad_size_description;             // string(255)  
    var $ad_price;                        // real(11)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Ad_sizes',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array ('ad_size_description', 'ad_price');
}
