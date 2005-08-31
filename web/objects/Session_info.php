<?php
/**
 * Table Definition for session_info
 */
require_once 'DB/DataObject.php';

class Session_info extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'session_info';                    // table name
    var $session_id;                      // string(32)  not_null primary_key unique_key
    var $ip_addr;                         // string(20)  
    var $entered;                         // datetime(19)  binary
    var $updated;                         // timestamp(19)  not_null unsigned zerofill binary timestamp
    var $user_id;                         // int(32)  
    var $vars;                            // blob(65535)  blob binary

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Session_info',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_linkDisplayFields = array('ip_addr', 'updated',
									  'user_id');

	var $fb_fieldLabels = array (
		'session_id' => 'PHP SessionID',
		'ip_addr' => 'IP Address',
		'updated' => 'Last Page View',
		'user_id' => 'User ID',
		'vars' => 'Serialised PHP vars saved'
		);
	var $fb_fieldsToRender = array (
		'ip_addr',
		'updated' ,
		'user_id'
		);
	var $fb_formHeaderText =  'Login History';

    // details appear to be broken on this
    var $fb_recordActions = array();
    var $fb_viewActions = array();


}
