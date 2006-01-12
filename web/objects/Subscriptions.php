<?php
/**
 * Table Definition for subscriptions
 */
require_once 'DB/DataObject.php';

class Subscriptions extends CoopDBDO 
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
    var $fb_requiredFields = array ('realm_id', 'user_id');

    function preGenerateForm($form)
        {
            // XXX super butt ugly, with cheeze
            // i suspect this has been totally redone now with getlinkoptions
            $this->fb_defaults['user_id'] = $form->CoopForm->page->auth['uid'];

            //XXX this is HIDEOUS!! injecting family_id in there by force. EVIL!
            //but ispermittedfield needs this, so i've no choice
            $fam = $this->factory('users');
            $fam->user_id = $this->user_id;
            $fam->find(true);
            $this->family_id = $fam->family_id;
        }


    function fb_linkConstraints(&$co)
        {
            $fam = $this->factory('users');
            $this->joinAdd($fam);
            //$this->selectAdd('family_id');
            /// XXX isn't this constrainfamily????
            if($co->isPermittedField(NULL) < ACCESS_VIEW ){
                //XXX constrainfamily won't work, because i use userid here
                $this->whereAdd(
                    sprintf('%s.user_id = %d',
                            $co->table, $co->page->auth['uid']));
            }
            //$co->debugWrap(2);
        }


}
