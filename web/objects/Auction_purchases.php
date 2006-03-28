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

    var $fb_shortHeader = 'Purchases';


    function preGenerateForm(&$form)
        {

            /// I ONLY WANT THE PACKAGE NUMBER REALLY
            $el =& $form->createElement(
                'customselect', 
                $form->CoopForm->prependTable('package_id'), false);
            $pkg =& new CoopObject(&$form->CoopForm->page, 'packages', 
                                   &$form->CoopForm);

            $pkg->obj->query(
                sprintf(
                    'select package_id, 
concat(package_types.prefix, package_number) as package_number,
packages.package_title from packages 
left join package_types on packages.package_type_id = package_types.package_type_id where packages.school_year = "%s"
order by package_types.sort_order, cast(packages.package_number as signed), packages.package_title, packages.package_description
',
                    $form->CoopForm->getChosenSchoolYear()));
            $el->setValue($this->package_id);
            
            
            $el->_parentForm =& $form;
            $el->prepare(&$pkg);

            $this->fb_preDefElements['package_id'] =& $el;


            /// I ONLY WANT THE PADDLE NUMBER REALLY
            $el =& $form->createElement(
                'customselect', 
                $form->CoopForm->prependTable('springfest_attendee_id'), false);
            $pad =& new CoopObject(&$form->CoopForm->page, 'springfest_attendees', 
                                   &$form->CoopForm);

            $pad->obj->query(
                sprintf(
'select * from springfest_attendees
where springfest_attendees.school_year = "%s"
order by springfest_attendees.paddle_number
',
                    $form->CoopForm->getChosenSchoolYear()));
            $el->setValue($this->springfest_attendee_id);
            
            
            $el->_parentForm =& $form;
            $el->prepare(&$pad);

            $this->fb_preDefElements['springfest_attendee_id'] =& $el;




        }

    function fb_display_view(&$co)
        {

            $co->schoolYearChooser();            
            
            $this->query(sprintf("
select distinct
auction_purchases.auction_purchase_id,
concat(package_types.prefix, package_number) as package_number,
packages.package_title,
packages.package_value,
auction_purchases.package_sale_price,
(auction_purchases.package_sale_price - packages.package_value) as variance,
coalesce(companies_income_join.family_id, invitations.family_id, 
        parents.family_id) as family_id,
packages.school_year
from packages
left join  auction_purchases on auction_purchases.package_id = 
      packages.package_id
left join package_types on packages.package_type_id = package_types.package_type_id
left join springfest_attendees 
     on auction_purchases.springfest_attendee_id = springfest_attendees.springfest_attendee_id
left join parents on springfest_attendees.parent_id = parents.parent_id
left join companies_income_join on springfest_attendees.company_id = companies_income_join.company_id
left join invitations on springfest_attendees.lead_id = invitations.lead_id
where packages.school_year = '%s'
and auction_purchases.package_id is not null
group by auction_purchases.auction_purchase_id
order by variance desc, auction_purchases.package_sale_price desc, 
packages.package_type_id, packages.package_number
",
                                 $co->getChosenSchoolYear()));

            $this->preDefOrder = array('package_number', 
                                             'package_title', 
                                             'package_value',
                                             'variance',
                                             'package_sale_price'
                );

            $this->fb_fieldLabels = array(
                "package_number" => 'Package Number',
                "package_title" => "Package Title (short)" ,
                "package_value" => 'Estimated Value ($)' ,
                'variance' => 'Variance (over asking price)',
                'package_sale_price' => 'Actual Sale Price');

            array_push($this->fb_currencyFields, 'variance');
            return $co->simpleTable(false, true);

        }


}
