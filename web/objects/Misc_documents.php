<?php
/**
 * Table Definition for misc_documents
 */
require_once 'CoopDBDO.php';

class Misc_documents extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'misc_documents';                  // table name
    var $misc_documents_id;               // int(32)  not_null primary_key unique_key auto_increment
    var $body;                            // blob(16777215)  blob
    var $_cache_body;                     // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Misc_documents',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
