<?php
/**
 * Table Definition for brings_baby
 */
require_once 'DB/DataObject.php';

class Brings_baby extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'brings_baby';                     // table name
    var $bring_baby_id;                   // int(32)  not_null primary_key unique_key auto_increment
    var $worker_id;                       // int(32)  
    var $baby_due_date;                   // date(10)  binary
    var $baby_too_old_date;               // date(10)  binary

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Brings_baby',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_fieldLabels = array ('worker_id' => 'Worker',
                                 'baby_due_date' => 'Baby Due',
                                 'baby_too_old_date' => 'Baby too old to bring');

    var $fb_requiredFields = array('worker_id', 'baby_due_date', 
                                   'baby_too_old_date');

	var $fb_formHeaderText =  'Workers Bringing Babies';
	var $fb_shortHeader =  'Babies';
	var $fb_linkDisplayFields =  array('worker_id', 'baby_due_date');


     var $fb_joinPaths = array(
         'family_id' => 'workers:parents',
         'school_year' => 'workers'
         );

	function fb_linkConstraints(&$co)
		{

            $workers = new CoopObject(&$co->page, 'workers', &$co);
            $workers->linkConstraints();

            $this->joinAdd($workers->obj);


 		}



}
