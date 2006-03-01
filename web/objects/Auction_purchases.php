<?php
/**
 * Table Definition for auction_purchases
 */
require_once 'DB/DataObject.php';

class Auction_purchases extends CoopDBDO 
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
	var $fb_formHeaderText =  'Springfest Auction Purchases';
	var $fb_currencyFields = array('package_sale_price');

    var $fb_recordActions = array();
    var $fb_viewActions = array();
    var $fb_shortHeader = 'Purchases';


    function fb_display_view(&$co)
        {

            //XXXX concat the new package_types.prefix too!

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

            $this->preDefOrder = array('package_number', 
                                             'package_title', 
                                             'package_value',
                                             'variance',
                                             'package_sale_price'
                );
            $this->fb_fieldLabels['variance'] = 'Variance (over asking price)';
            $this->fb_fieldLabels['package_sale_price'] = 'Actual Sale Price';
            array_push($this->fb_currencyFields, 'variance');
            return $co->simpleTable(false, true);

        }


}
