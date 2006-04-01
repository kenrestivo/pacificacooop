<?php
/**
 * Table Definition for thank_you_templates
 */
require_once 'CoopDBDO.php';

class Thank_you_templates extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'thank_you_templates';             // table name
    var $thank_you_template_id;           // int(32)  not_null primary_key unique_key auto_increment
    var $cash;                            // string(255)  
    var $ticket;                          // string(255)  
    var $value_received;                  // string(255)  
    var $no_value;                        // string(255)  
    var $ad;                              // string(255)  
    var $main_body;                       // blob(16777215)  blob
    var $_cache_main_body;                // string(255)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Thank_you_templates',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


	var $fb_linkDisplayFields = array('school_year');

	var $fb_textFields = array ('main_body'); 

	var $fb_fieldLabels = array( 
        'main_body' => 'Main Body of the Letter',   
       'cash' => 'Cash Received Fragment',   
        'ticket' => 'Text for Ticket Purchased',      
        'value_received' => 'Text for Value Received', 
        'no_value' => 'Text when No Value was Received',     
        'ad' => 'Text for Ads',           
        'school_year' => 'School Year'
		);


    var $preDefOrder = array('main_body',  'cash', 'ticket', 'value_received' , 
                             'no_value' , 'ad' , 'school_year');

	var $fb_formHeaderText =  'Springfest Thank You Templates';

    var $fb_shortHeader = 'Template';

    // everything except school year! clunky, but it works: only one per year
    var $fb_dupeIgnore = array('cash' , 'ticket' , 'value_received' , 
                               'no_value' , 'ad', 'main_body' , 
                               '_cache_main_body');

	var $fb_requiredFields = array(
        'cash', 
        'ticket', 
        'value_received',
        'no_value',
        'ad',
        'main_body',
        'school_year'
        );

    var $fb_defaults = array(
        'value_received' => "In exchange for your contribution, we gave you",
        'no_value' => "For tax purposes, no goods or services were provided in exchange for your contribution",
        'cash' => 'cash for our Springfest fundraiser',
        'ad' => 'ad valued at',
        'ticket' => 'to the Springfest event valued altogether at'
        );
    

    var $fb_sizes = array(
        'cash' => 50,
        'ticket' => 50,
        'no_value' => 100,
        'value_received' => 50
        );


}
