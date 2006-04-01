<?php
/**
 * Table Definition for thank_you_templates
 */
require_once 'CoopDBDO.php';

class Thank_you_templates extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'thank_you_templates';             // table name
    var $thank_you_template_id;           // int(32)  not_null primary_key unique_key auto_increment
    var $cash;                            // string(255)  
    var $ticket;                          // string(255)  
    var $value_received;                  // string(255)  
    var $no_value;                        // string(255)  
    var $ad;                              // string(255)  
    var $main_body;                       // blob(16777215)  blob
    var $_cache_main_body;                // string(255)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Thank_you_templates',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
