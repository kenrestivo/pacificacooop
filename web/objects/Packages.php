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
    var $display_publicly;                // string(3)  enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Packages',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_textFields = array ('package_description');
	var $fb_linkDisplayFields = array ('package_number', 
									   'package_description');
	var $fb_enumFields = array ('item_type', 'package_type', 'display_publicly');

	var $fb_fieldsToRender = array('package_type', 'package_number', 
								   'package_title', 'package_description', 
								   'item_type', 'package_value',
								   'display_publicly');

	var $fb_fieldLabels = array (
		"package_id" => "Package ID" ,
		"package_type" => "Package Type" ,
		"package_number" => "Package Number (as it will be printed in program)" ,
		"package_title" => "Package Title (short)" ,
		"package_description" => "Package Description (long)" ,
		"donated_by_text" => "Generously Donated By" ,
		"item_type" => "Physical Product or Gift Certificate",
		"package_value" => 'Estimated Value ($)' ,
		"starting_bid" => 'Starting Bid ($)' ,
		'display_publicly' => 'Display on public home page?',
		"bid_increment" => 'Bid Increment ($)'
		);
	var $fb_formHeaderText = "Springfest Packages";

	var $fb_requiredFields = array('package_description', 'donated_by_text', 
								   'starting_bid', 'bid_increment', 
								   'package_type', 'package_value', 
								   'item_type', 'package_number');


	var $fb_crossLinks = array(array('table' => 'auction_packages_join', 
									 'toTable' => 'auction_donation_items',
									 'toField' => 'auction_donation_item_id',
									 'type' => 'select'));


	function constrainedPackagePopup($schoolyear = false)
		{
			$schoolyear = $schoolyear ? $schoolyear : findSchoolYear();

			$this->whereAdd(sprintf('%s.school_year = "%s"',
									$this->__table, $schoolyear));
			$this->orderBy('package_number, package_title, package_description');
			$this->find();
			$options[''] = '-- CHOOSE ONE --';
			while($this->fetch()){
				$options[$this->package_id] = 
					sprintf("%.42s...", 
							implode(' - ', 
									array(
										$this->package_number, 
										$this->package_title, 
										$this->package_description)));
			}
			$el =& HTML_QuickForm::createElement('select', 'package_id', 
												 $this->fb_fieldLabels['package_id'], 
												 &$options);

			return $el;
		}


}

