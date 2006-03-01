<?php
/**
 * Table Definition for package_types
 */
require_once 'CoopDBDO.php';

class Package_types extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'package_types';                   // table name
    var $package_type_id;                 // int(32)  not_null primary_key unique_key auto_increment
    var $package_type_short;              // string(50)  
    var $long_description;                // string(255)  
    var $sort_order;                      // int(3)  
    var $prefix;                          // string(3)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Package_types',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array ('package_type_short');
	var $fb_formHeaderText = "Springfest Package Types";
    var $fb_shortHeader = 'Types';

    var $fb_dupeIgnore = array(
        'sort_order',
        'long_description',
        'prefix'
        );

	var $fb_fieldLabels = array (
		"package_type_id" => "Package Type ID" ,
		"package_type_short" => "Package Type" ,
        'prefix' => 'Single-letter Prefix (to appear before package number)',
        'long_description' => 'Extended Description',
        'sort_order' => 'Sort in This Order'
        );

	var $fb_requiredFields = array('package_type_short',
                                   'prefix',
								   'sort_order');
    
    var $fb_sizes = array('prefix' => 2);


    function fb_linkConstraints(&$co)
		{
            // TODO: let them define different package typesf or different schoolyears?
            $co->obj->orderBy('package_types.sort_order, package_types.package_type_short');

        }

}
