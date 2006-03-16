<?php
/**
 * Table Definition for votes
 */
require_once 'CoopDBDO.php';

class Votes extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'votes';                           // table name
    var $vote_id;                         // int(32)  not_null primary_key unique_key auto_increment
    var $family_id;                       // int(32)  
    var $question_id;                     // int(32)  
    var $answer_id;                       // int(32)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Votes',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
