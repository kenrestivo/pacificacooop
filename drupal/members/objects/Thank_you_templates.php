<?php
/**
 * Table Definition for thank_you_templates
 */
require_once 'COOP/DBDO.php';

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

    var $fb_shortHeader = 'Templates';

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
        'ticket' => 'to the Springfest event valued altogether at',
        'main_body' =>  '[:DATE:]<br /> <br /> [:NAME:]<br /> [:ADDRESS:]<br /> <br /> Dear [:DEAR:],<br /> <br /> Thank you for your kind donation of [:ITEMS:] to our [:ITERATION:]<sup>[:ORDINAL:]</sup> Annual Springfest [:YEAR:] Wine Tasting and Auction. <br /> <br /> [:VALUERECEIVED:].<br /> <br /> Because of the support of our community this year, we were able to raise the amount of money needed to make the necessary repairs and improvements to our nursery school.&nbsp; For [:YEARS:] years, the Pacifica Co-op Nursery School has provided an enriching experience for both children and parents of our community. <br /> <br /> The Pacifica Co-op Nursery School is a non-profit, parent participation program.&nbsp; We rely on the assistance of the community in conjunction with friends and family to meet our ever-increasing costs.&nbsp; Again, we thank you for considering the Pacifica Co-op Nursery School a deserving place to offer your community support.<br /> <br /> <div style="text-align: center"><em><strong>&quot;An investment in our children is an investment in our community.&quot;</strong></em><br />   </div>  <br /> Sincerely,<br /> <br /> [:FROM:]<br /> <br /> Pacifica Co-op Nursery School <br /> Incorporated as &quot;Pacifica Nursery School, Inc.&quot;<br /> A 501(c)(3) non-profit organization<br /> Tax ID # 94-1527749 <br />'
        );
    

    var $fb_sizes = array(
        'cash' => 50,
        'ticket' => 50,
        'no_value' => 100,
        'value_received' => 50
        );


}
