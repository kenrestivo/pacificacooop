<?php
/**
 * Table Definition for packages
 */
require_once 'DB/DataObject.php';

class Packages extends CoopDBDO 
{
	var $textFields = array ('package_description');
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'packages';                        // table name
    var $package_id;                      // int(32)  not_null primary_key unique_key auto_increment
    var $package_number;                  // int(5)  
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
    var $_cache_package_description;      // string(255)  
    var $package_type_id;                 // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Packages',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_textFields = array ('package_description');
	var $fb_linkDisplayFields = array ('package_type_id',
                                       'package_number', 
                                       'package_title',
									   'package_description');
	var $fb_enumFields = array ('item_type', 'package_type', 'display_publicly');

    var $fb_shortHeader = 'Packages';
    var $fb_dupeIgnore = array(
        'package_type_id',
        'donated_by_text',
        'item_type',
        'package_value',
        'starting_bid',
        'bid_increment'
        );

	var $fb_fieldLabels = array (
		"package_id" => "Package ID" ,
		"package_type_id" => 'Package Type' ,
		"package_number" => 'Package Number',
		"package_title" => "Package Title (short)" ,
		"package_description" => "Package Description (long)" ,
		"donated_by_text" => "Generously Donated By" ,
		"item_type" => "Physical Product or Gift Certificate",
		'school_year' => 'School Year',
		"package_value" => 'Estimated Value ($)' ,
		"starting_bid" => 'Starting Bid ($)' ,
		'display_publicly' => 'Display on public home page?',
		"bid_increment" => 'Bid Increment ($)'
		);
	var $fb_formHeaderText = "Springfest Packages";


var $fb_defaults = array(
    'package_type_id' => COOP_PACKAGE_TYPE_SILENT,
    'display_publicly' => 'No'
);

var $fb_currencyFields = array(
   'package_value',
   'starting_bid',
   'bid_increment'
);
    var $fb_displayCallbacks = array('package_value' => 'priceless',
                                     'package_number' => 'prefixLetter');

	var $fb_requiredFields = array('package_description', 
								   'starting_bid', 'bid_increment', 
								   'school_year',
								   'package_type_id', 
								   'item_type', 'package_number');


	var $fb_crossLinks = array(array('table' => 'auction_packages_join', 
									 'toTable' => 'auction_donation_items',
									 'toField' => 'auction_donation_item_id',
									 'type' => 'select'));

   var $fb_sizes = array(
     'package_description' => 100
   );

    var $fb_extraDetails = array('auction_packages_join:auction_donation_items');

    function fb_linkConstraints(&$co)
		{
            $par = new CoopObject(&$co->page, 'package_types', &$co);
            $co->protectedJoin($par);
            $co->constrainSchoolYear();
            $co->obj->selectAdd('package_types.prefix');
            // XXX for some reason, i still have to cast this thing
            // to get it to sort properly
            $co->obj->orderBy('package_types.sort_order, cast(packages.package_number as signed), packages.package_title, packages.package_description');

        }
    

    function postGenerateForm(&$form)
        {

            $js = sprintf(
                'setPackageDefaults = function(self){
                        var f = document.getElementById("%s");
                        f["%s"].value = Math.round(Math.ceil(self.value / %d) / %d) * %d;
                        f["%s"].value = Math.round(Math.ceil(self.value / %d) / %d) * %d;
                }',
                $form->_attributes['id'],
                $form->CoopForm->prependTable('starting_bid'),
                COOP_DEFAULT_STARTING_BID_DIVISOR,
                COOP_DEFAULT_STARTING_BID_CLAMP, 
                COOP_DEFAULT_STARTING_BID_CLAMP,
                $form->CoopForm->prependTable('bid_increment'),
                COOP_DEFAULT_BID_INCREMENT_DIVISOR,
                COOP_DEFAULT_BID_INCREMENT_CLAMP,
                COOP_DEFAULT_BID_INCREMENT_CLAMP);

            $form->addElement('static', 
                              'setPackageDefaults_script', '',
                              wrapJS($js));
            
            $form->updateElementAttr(
                array($form->CoopForm->prependTable('package_value')), 
                array('onchange' => 'setPackageDefaults(this)'));


            // if there is no package number, get max set default to max
            if($this->package_number < 1){
                $temp =& $this->factory($this->__table);
                $temp->query(sprintf(
                                 'select max(package_number) as max_package
                 from %s
                where package_type_id = %d and school_year = "%s"',
                                 $this->__table,
                                 COOP_PACKAGE_TYPE_SILENT,
                    $form->CoopForm->getChosenSchoolYear()));
                $temp->fetch();
                
                $form->setDefaults(
                    array($form->CoopForm->prependTable('package_number') => 
                          (int) $temp->max_package + 1));
            }
        }



        function fb_display_view(&$co)
        {
            $co->schoolYearChooser();
            $res ="";
            
            if($co->isPermittedField(null, true, true) >=  ACCESS_EDIT){
                $res .= $co->page->selfURL(
                    array('value' => 'Change Permament Sort Order',
                          'inside' => array('action' => 'view',
                                            'table' => 'package_types',
                                            'push' => $co->table)));

                $res .= $co->page->selfURL(
                    array('value' => 'Renumber All Packages',
                          'base' => 'maint/renumber-packages.php',
                          'tags' => array('title' => 
                                          'Fixes holes in package number sequence'),
                          'inside' => array('action' => 'view',
                                            'table' => 'package_types')));


            }


            //// ADD SHOW DETAILS BUTTON
                $longdescr =& $co->searchForm->addElement(
                    'advcheckbox', 
                    'show_long_description', 
                    'Show long description?',
                    '(page may take forever to display if checked)', 
                    array('onchange' =>
                          'this.form.submit()')) ; 


                $co->searchForm->setDefaults(
                    // NOTE! isset not empty! preserve nulls!
                    array('show_long_description' => 
                          isset($co->page->vars['last']['show_long_description']) ? $co->page->vars['last']['show_long_description'] : 0));


                $show_long_description = $longdescr->getValue();
                $co->page->vars['last']['show_long_description'] = $show_long_description;
                $co->fullText = $show_long_description;



                $co->showChooser = 1;
                $co->searchForm->addElement('submit', 'savebutton', 'Change');



            $res .= $co->simpleTable(true, true);
            return $res;
        }


// set package_description lines = 3

  function priceless(&$co, $val, $key)
        {
            if($val <= 0){
                return 'Priceless';
            }
            return sprintf('$%0.02f', $val);
        }


  function prefixLetter(&$co, $val, $key)
        {
            return sprintf('%s%02d', 
                           $this->prefix,
                           $val);
        }


/// XXX NOTE THIS FUNCTION NEEDS TO BE REWRITTEN!
/// it does not use the proper format for inclusion here in the dataobject
/// it needs to also return a hashtable(array) which can then be formatted
/// by the caller in whatever CSS or javascript way is needed
function public_packages(&$cp, $sy)
{
	$res .= sprintf('<hr>
	<p>Here are some fabulous items that will be auctioned off 
			at  Springfest!</p>');

	$tab =& new HTML_Table();
	$tab->addRow(array('Item Number',
					   'Item Name',
					   'Description',
					   'Value'), 
				 'bgcolor=#aabbff align=left', 'TH');

    // leading zeroes
	$q = sprintf('select concat(package_types.prefix, package_number),
       package_title, package_description,
        package_value
        from packages
        left join package_types on packages.package_type_id = package_types.package_type_id
		where display_publicly = "Yes"
				and school_year = "%s"
order by package_types.sort_order, cast(packages.package_number as signed), packages.package_title, packages.package_description',
                 $sy);

	$listq = mysql_query($q);
	$i = 0;
	$err = mysql_error();
	if($err){
		user_error("public_auction($title): [$q]: $err", E_USER_ERROR);
	}
	if(mysql_num_rows($listq) < 1){
		return "<p>Coming soon! Watch this space for fabulous auction items.</p>";
	}
	while($row = mysql_fetch_assoc($listq)){
		$tdrow = array();
		while ( list( $key, $val ) = each($row)) {
			if($key == 'package_value'){
				if($val < 1){
					$tdrow[] = "Priceless";
				} else {
					$tdrow[] = sprintf("$%0.2f",$val);
				}
			} else {
				$tdrow[] = $val;
			}
		}
		$tab->addRow($tdrow, 'style="tableheader"');
	}
    $tab->altRowAttributes(1, 'class="altrow1"', 
                           'class="altrow2"');


	return $res . $tab->toHTML();
} // end public packages




// bump all package numbers ahead of the current one
function incrementPackages()
        {
            $fixer =& $this->__clone();
            
            ///NOTE! the package number here in the DBObject is the NEW one

            $fixer->query(
                sprintf(
                    'update %s
set package_number = package_number + 1
where  package_number  >= %d
and package_type_id = %d and school_year = "%s"',
                    $this->__table,
                    $this->package_number,
                    $this->package_type_id,
                    $this->school_year));
        }


// fill up hole left by deleted package
function decrementPackages()
        {
            $fixer =& $this->__clone();

            ///NOTE! the package number here in the DBObject is the NEW one
            ///so we have to go fish out the old one to find its number
            // and don't dare clone it! bad things happen when you clone.
            $old =& $this->factory($this->__table);
            $old->get($this->{$this->CoopObject->pk});

            $fixer->query(
                sprintf(
                    'update %s
set package_number = abs(package_number - 1)
where  package_number  >= %d
and package_type_id = %d and school_year = "%s"',
                    $this->__table,
                    $old->package_number,
                    $this->package_type_id,
                    $this->school_year));
        }



//XXX all functions below are potentially racey in a multi-user environment
//i should probably do some transaction commit/rollback here

function update()
        {
            // clamp. people will put dashes in; avoid Trouble
            $this->package_number = abs($this->package_number);

            /// does it matter what order i do this in? this seems right.
            $this->decrementPackages();
            $this->incrementPackages();
            parent::update();
        }

function insert()
        {
            // clamp. people will put dashes in; avoid Trouble
            $this->package_number = abs($this->package_number);

            $this->incrementPackages();
            parent::insert();
        }


function delete()
        {
            $this->decrementPackages();
            parent::delete();
        }


}

