<?php
/**
 * Table Definition for answers
 */
require_once 'CoopDBDO.php';

class Answers extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'answers';                         // table name
    var $answer_id;                       // int(32)  not_null primary_key unique_key auto_increment
    var $question_id;                     // int(32)  
    var $answer;                          // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Answers',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
