<?php
/**
 * Table Definition for books
 */
require_once 'CoopDBDO.php';

class Books extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'books';                           // table name
    var $books_id;                        // int(32)  not_null primary_key unique_key auto_increment
    var $isbn;                            // string(10)  
    var $title;                           // string(255)  
    var $authors;                         // string(255)  
    var $book_color_id;                   // int(32)  
    var $primary_book_category_id;        // int(32)  
    var $secondary_book_category_id;      // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Books',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
