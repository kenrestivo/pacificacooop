<?php
/**
 * Table Definition for blog_entry
 */
require_once 'DB/DataObject.php';

class Blog_entry extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'blog_entry';                      // table name
    var $blog_entry_id;                   // int(32)  not_null primary_key unique_key auto_increment
    var $parent_id;                       // int(32)  
    var $short_title;                     // string(255)  
    var $body;                            // blob(16777215)  blob
    var $show_on_members_page;            // string(7)  enum
    var $show_on_public_page;             // string(7)  enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Blog_entry',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('show_on_members_page', 
								'show_on_public_page');
}
