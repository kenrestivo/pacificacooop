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
    var $family_id;                       // int(32)  
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
	var $fb_linkDisplayFields = array('short_title');
	var $fb_fieldLabels = array ('family_id' => 'Entered by Co-Op Family',
								 'short_title' => 'Headline',
								 'body' => 'Story',
								 'show_on_members_page' => 'OK to show on members-only page?',
								 'show_on_public_page' => 'OK to show on public web-site'
		);
	var $fb_fieldsToRender = array('family_id', 'short_title', 'body', 
                                   'show_on_members_page', 
								   'show_on_public_page');
	var $fb_formHeaderText =  'Breaking News';
	var $fb_textFields = array('body');
	var $fb_requiredFields = array('family_id', 'short_title', 'body');
	var $fb_defaults = array('show_on_members_page' => 'Yes',
                             'show_on_public_page' => 'No');
}
