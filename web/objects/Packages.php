<?php
/**
 * Table Definition for packages
 */
require_once 'DB/DataObject.php';

class Packages extends DB_DataObject 
{
	var $textFields = array ('package_description');
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'packages';                        // table name
    var $package_id;                      // int(32)  not_null primary_key unique_key auto_increment
    var $package_type;                    // string(8)  enum
    var $package_number;                  // string(20)  
    var $package_title;                   // string(255)  
    var $package_description;             // blob(16777215)  blob
    var $package_value;                   // real(11)  
    var $item_type;                       // string(16)  enum
    var $donated_by_text;                 // string(255)  
    var $starting_bid;                    // real(11)  
    var $bid_increment;                   // real(11)  
    var $school_year;                     // string(50)  
    var $wish_list;                       // string(3)  enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Packages',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_textFields = array ('package_description');
	var $fb_linkDisplayFields = array ('package_number', 
									   'package_description');
	var $fb_enumFields = array ('item_type', 'package_type');

	var $fb_fieldsToRender = array('package_type', 'package_number', 'package_title', 'package_description', 
								   'item_type', 'package_value');

	var $fb_fieldLabels = array (
		"package_type" => "Package Type" ,
		"package_number" => "Package Number (as it will be printed in program)" ,
		"package_title" => "Package Title (short)" ,
		"package_description" => "Package Description (long)" ,
		"donated_by_text" => "Generously Donated By" ,
		"item_type" => "Physical Product or Gift Certificate",
		"package_value" => 'Estimated Value ($)' ,
		"starting_bid" => 'Starting Bid ($)' ,
		"bid_increment" => 'Bid Increment ($)', 
		"school_year" => "School Year (YYYY-YYYY)" 

		);
	var $kr_longTitle = "Springfest Packages";
}

