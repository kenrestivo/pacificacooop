<?php
/**
 * Table Definition for tickets
 */
require_once 'DB/DataObject.php';

class Tickets extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'tickets';                         // table name
    var $ticket_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $income_id;                       // int(32)  
    var $ticket_quantity;                 // int(5)  
    var $lead_id;                         // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Tickets',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
