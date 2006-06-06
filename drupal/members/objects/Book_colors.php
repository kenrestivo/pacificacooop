<?php
/**
 * Table Definition for book_colors
 */
require_once 'COOP/DBDO.php';

class Book_colors extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'book_colors';                     // table name
    var $book_color_id;                   // int(32)  not_null primary_key unique_key auto_increment
    var $name;                            // string(255)  
    var $hex_value;                       // string(8)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Book_colors',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('name');

	var $fb_fieldLabels = array(
        'name' => 'Color Name',
        'hex_value' => 'Color Hex Value (not yet used)'
		);

	var $fb_formHeaderText =  'Library Book Colors';

	var $fb_requiredFields = array('name');


    var $fb_shortHeader = 'Colors';

    
    var $fb_dupeIgnore = array('hex_value');





}
