<?php
/**
 * Table Definition for auction_purchases
 */
require_once 'DB/DataObject.php';

class Auction_purchases extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'auction_purchases';               // table name
    var $auction_purchase_id;             // int(32)  not_null primary_key unique_key auto_increment
    var $springfest_attendee_id;          // int(32)  
    var $package_id;                      // int(32)  
    var $package_sale_price;              // real(11)  
    var $income_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Auction_purchases',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('package_id', 'package_sale_price', 
									  'springfest_attendee_id');
	var $fb_fieldLabels = array (
		'springfest_attendee_id' => 'Paddle Number',
		'package_id' => "Auction Package Purchased",
		'package_sale_price' => 'Final Bid Price',
		'income_id' => "Payment Information"
		);
	var $fb_fieldsToRender = array (
		'package_id',
//		'income_id',
		'package_sale_price'
		);
	var $fb_formHeaderText =  'Springfest Auction Purchases';
	var $fb_currencyFields = array('package_sale_price');

    var $fb_recordActions = array();
    var $fb_viewActions = array();
    var $fb_shortHeader = 'Purchases';
    var $fb_longHeader = 'This is the "Jane Report": the actual purchases of Springfest Packages, along with their final bid price, and the variance between what they sold for and their estimated value. The most popular packages (those that sold for at or above their estimated value) are listed first.';



    function fb_display_view(&$co)
        {


            //TODO: school year chooser!

            $this->query(sprintf("
select distinct
packages.package_number,
packages.package_title,
packages.package_value,
auction_purchases.package_sale_price,
(auction_purchases.package_sale_price - packages.package_value) as variance
from packages
left join  auction_purchases on auction_purchases.package_id = 
      packages.package_id
where packages.school_year = '%s' 
order by variance desc",
                                 findSchoolYear()		  
                             ));

            $this->fb_fieldsToRender = array('package_number', 
                                             'package_title', 
                                             'package_value',
                                             'variance',
                                             'package_sale_price'
                );
            $this->fb_fieldLabels['variance'] = 'Variance (over asking price)';
            $this->fb_fieldLabels['package_sale_price'] = 'Actual Sale Price';
            array_push($this->fb_currencyFields, 'variance');
            return $co->simpleTable(false);

        }


}
