<?php
/**
 * Table Definition for instructions
 */
require_once 'CoopDBDO.php';

class Instructions extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'instructions';                    // table name
    var $instruction_id;                  // int(32)  not_null primary_key unique_key auto_increment
    var $table_name;                      // string(255)  
    var $action;                          // string(6)  enum
    var $instruction;                     // blob(16777215)  blob

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Instructions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
