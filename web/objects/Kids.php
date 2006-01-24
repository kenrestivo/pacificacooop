<?php
/**
 * Table Definition for kids
 */
require_once 'DB/DataObject.php';

class Kids extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'kids';                            // table name
    var $kid_id;                          // int(32)  not_null primary_key unique_key auto_increment
    var $last_name;                       // string(255)  
    var $first_name;                      // string(255)  
    var $family_id;                       // int(32)  
    var $date_of_birth;                   // date(10)  binary
    var $allergies;                       // string(255)  
    var $doctor_id;                       // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Kids',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('last_name','first_name');
	var $fb_fieldLabels = array ('last_name' => 'Last Name');
	var $fb_linkOrderFields = array ('last_name', 'first_name');
	var $fb_fieldLabels = array(
		'first_name' => "First Name",
		'last_name' => "Last Name",
		'family_id' => "Co-Op Family",
		'date_of_birth' => 'Birthday',
		'allergies' => 'Allergies',
        'doctor_id' => 'Doctor'
	);
	var $fb_formHeaderText = "Co-Op Students";
    var $fb_shortHeader = 'Students';

	var $fb_requiredFields  = array('last_name', 'first_name', 'family_id');

    var $fb_joinPaths = array('school_year' => 'enrollment');


	function fb_linkConstraints(&$co)
		{

            // NOTE i do this the easy way: i don't constrain to families
            // just to enrollment, because it's the shortest path
            $enrollment =  new CoopObject(&$co->page, 'enrollment', &$co);


            // HACK! this is presuming VIEW, but in popup it could be EDIT


            $co->protectedJoin($enrollment, 'left');

            // IMPORTANT! otherwise it matches old year
            $this->selectAdd("max(enrollment.school_year) 
                                                as school_year");

            $co->constrainSchoolYear();



            $this->selectAdd();
            $this->selectAdd("{$co->table}.*");


            $co->orderByLinkDisplay();
            $co->grouper();


            //$co->debugWrap(2);

			// ugly, but consisent. only shows families for this year
            
            
            
 		}

    
    function fb_display_view(&$co)
        {
            // for doctors
            // HMM. how to override for the coopform stuff too? pregenerate?
            $co->overrides['leads']['fb_linkDisplayFields'] = 
                array('last_name', 'first_name', 'phone');
            
            return $co->simpleTable(true,true);
        }
    

    function preGenerateForm(&$form)
        {
            // the more "modern" way to do pregenerate form
            $el =& $form->createElement(
                'customselect', 
                $form->CoopForm->prependTable('family_id'), false);

            $inc =& new CoopObject(&$form->CoopForm->page, 'families', 
                                   &$form->CoopForm);
            

            // EEEVIL EVIL HACK!
            // XXX ugly and dumb. put in a findlinkoption callback
            if($inc->isPermittedField(NULL,true) < ACCESS_EDIT){
                $inc->findLinkOptions();
            } else {
                $inc->obj->find(); 
            }
            $el->setValue($this->family_id);


            $el->_parentForm =& $form;
            $el->prepare(&$inc);

            $this->fb_preDefElements['family_id'] =& $el;


        }

}
