<?php
/**
 * Table Definition for subscriptions
 */
require_once 'DB/DataObject.php';

class Subscriptions extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'subscriptions';                   // table name
    var $subscription_id;                 // int(32)  not_null primary_key unique_key auto_increment
    var $realm_id;                        // int(32)  
    var $alerts;                          // int(1)  
    var $new_entries;                     // int(1)  
    var $changes;                         // int(1)  
    var $user_id;                         // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Subscriptions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_fieldLabels = array(
		'realm_id' => 'Realm',
        'alerts' => 'Email Alerts or Warnings',
        'new_entries' => 'Email Notice of New Entries',
        'changes' => 'Email All Changes',
        'user_id' => 'User'
        ); 

    var $fb_linkDisplayFields = array('realm_id', 'user_id');
	var $fb_formHeaderText =  'Email Subscriptions';
    var $fb_shortHeader = 'Settings';
    var $fb_defaults = array('alerts' => 1);


}
