<?php
/**
 * Table Definition for audit_trail
 */
require_once 'DB/DataObject.php';

class Audit_trail extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'audit_trail';                     // table name
    var $audit_trail_id;                  // int(32)  not_null primary_key unique_key auto_increment
    var $table_name;                      // string(255)  
    var $index_id;                        // int(32)  
    var $audit_user_id;                   // int(32)  
    var $updated;                         // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Audit_trail',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_formHeaderText =  'Audit Trail';
	var $fb_linkDisplayFields = array();
	var $fb_fieldLabels = array (
		'table_name' => 'Name of Table',
		'index_id' => 'Unique ID',
		'audit_user_id' => 'Edited By',
		'updated' => 'Edited On'
		);
	var $fb_fieldsToRender = array ('audit_user_id', 'updated');

    // blow these off, they make no sense for details
    var $fb_recordActions = array();
    var $fb_viewActions = array();

}
