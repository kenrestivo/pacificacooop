<?php
/**
 * Table Definition for secondary_book_categories
 */
require_once 'CoopDBDO.php';

class Secondary_book_categories extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'secondary_book_categories';       // table name
    var $secondary_book_category_id;      // int(32)  not_null primary_key unique_key auto_increment
    var $name;                            // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Secondary_book_categories',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
