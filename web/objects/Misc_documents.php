<?php
/**
 * Table Definition for misc_documents
 */
require_once 'COOP/DBDO.php';

class Misc_documents extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'misc_documents';                  // table name
    var $misc_documents_id;               // int(32)  not_null primary_key unique_key auto_increment
    var $title;                           // string(255)  
    var $body;                            // blob(16777215)  blob
    var $_cache_body;                     // string(255)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Misc_documents',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


	var $fb_linkDisplayFields = array('title');

	var $fb_fieldLabels = array (
        'title' => 'Document Title',
        'body' => 'Body of document',
        'school_year' => 'School Year'
        );

    var $fb_requiredFields = array('body', 'title', 'school_year');

	var $fb_formHeaderText =  'Miscellaneous Documents';

    var $fb_shortHeader = 'Misc';

    var $fb_textFields = array('body');

    var $fb_sizes = array('body' => 100, 'title' => 50);
    var $fb_textHeight = array('body' => 100);

    var $fb_mceInitFile = COOP_ABSOLUTE_URL_PATH . '/lib/tinymce_hack.js'; // XXX nasty hack

}
