<?php
/**
 * Table Definition for primary_book_categories
 */
require_once 'COOP/DBDO.php';

class Primary_book_categories extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'primary_book_categories';         // table name
    var $primary_book_category_id;        // int(32)  not_null primary_key unique_key auto_increment
    var $name;                            // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Primary_book_categories',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('name');

	var $fb_fieldLabels = array(
        'name' => 'Category Name'	
        );

	var $fb_formHeaderText =  'Library Book Primary Categories';

	var $fb_requiredFields = array('name');


    var $fb_shortHeader = 'Primary';

    
}
