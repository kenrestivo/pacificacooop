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
		'updated' => 'Last Page View',
		'session_id' => 'PHP SessionID',
		'ip_addr' => 'IP Address',
		'user_id' => 'User ID',
		'vars' => 'Last Activity'
		);
	var $fb_fieldsToRender = array (
		'ip_addr',
		'updated' ,
		'user_id'
		);
	var $fb_formHeaderText =  'Login History';
	var $fb_shortHeader =  'Logins';

    // details appear to be broken on this
    var $fb_recordActions = array();
    var $fb_viewActions = array();
    var $fb_displayCallbacks = array('vars' => 'formatVars');


 	function fb_linkConstraints(&$co)
		{
            $fam = $this->factory('users');
            $this->joinAdd($fam);
            if($co->isPermittedField(NULL) < ACCESS_VIEW ){
                //XXX constrainfamily won't work, because i use userid here
                $this->whereAdd(
                    sprintf('%s.user_id = %d',
                            $co->table, $co->page->auth['uid']));
            }
            //$co->debugWrap(2);
            // don't show the homepage visits, only members
            $this->whereAdd(sprintf('%s.user_id > 0', $co->table));
            $this->orderBy('updated desc');
        }


    function fb_display_view(&$co)
        {
            $res = '';

            $res .= "<p>Times are in Mountain Time (Phoenix, AZ).</p>";
                        
            /// THE CHOOSER FORM
            $syform =& new HTML_QuickForm('sessioninfo', false, false, 
                                          false, false, true);
            $el =& $syform->addElement('text', 'limit', 'Records to show', 
                                       '20',
                                       array('onchange' =>
                                             'javascript:submitForm()'));

            if($sid = thruAuthCore($co->page->auth)){
                $syform->addElement('hidden', 'coop', $sid); 
            }
            $syform->addElement('hidden', 'table', $co->table); 


            // need change button?
            $syform->addElement('submit', 'savebutton', 'Change');
                
            
            //COOL! this is the first place i am using vars->last
            $syform->setDefaults(array('limit' =>20));
            
            $limit = $el->getValue();
            $this->limit($limit);
            

            $res .= $syform->toHTML();
            
            $res .= $co->simpleTable();
            
            return $res;
            
        }

    function formatVars(&$co, $val, $key)
        {
            /// XXX THIS DOESN'T WORK! the SESSION must be in a weird format?
            $vars = unserialize($val);
            return $val;
            return print_r($vars, 1);
        }

}
