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
    var $details;                         // blob(16777215)  blob

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Audit_trail',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_formHeaderText =  'Audit Trail';
	var $fb_shortHeader =  'Audit';
	var $fb_linkDisplayFields = array();
	var $fb_fieldLabels = array (
		'table_name' => 'Name of Table',
		'index_id' => 'Unique ID',
		'audit_user_id' => 'Edited By',
		'updated' => 'Edited On',
        'details' => 'What changed'
		);
	var $fb_fieldsToRender = array ('audit_user_id', 'updated', 'details');
    var $fb_recordActions = array();
    var $fb_viewActions = array();
    var $fb_displayCallbacks = array('details' => 'formatChanges');

    function fb_display_details()
        {
            //XXX move this to a session var or somethign. or provide a chooser
            $limit = $_REQUEST['limit'] ? $_REQUEST['limit'] : 20;
	 
            $session = new CoopView(&$this->CoopView->page, 'session_info', $none);

            // this is an interesting pseudo sub-dispatcher
            if($_REQUEST[$this->CoopView->prependTable($this->CoopView->pk)]){
                $this->{$this->CoopView->pk} = 
                    $_REQUEST[$this->CoopView->prependTable(
                                  $this->CoopView->pk)];
                    //array_push($this->fb_fieldsToRender, 'table_name', 'index_id');
                    $this->find(true);
                    //print $this->CoopView->horizTable();
			 
                    // basically the stuff in genericdetails.
                    $thing = new CoopView(&$this->CoopView->page, $this->table_name, &$nothing);
                    $thing->obj->{$thing->pk} = $this->index_id;
                    print $thing->horizTable();
			 
                    // standard audit trail, for all details
                    $aud =& new CoopView(&$this->CoopView->page, 'audit_trail', &$thing);
                    $aud->obj->table_name = $this->table_name;
                    $aud->obj->index_id = $this->index_id;
                    $aud->obj->orderBy('updated desc');
                    print $aud->simpleTable();
            }

            //XXX maybe do only if privileged
            if($_REQUEST[$session->prependTable($session->pk)]){
                $session->obj->{$session->pk} = 
                    $_REQUEST[$session->prependTable($session->pk)];
                    print $session->horizTable();
		 
                    $this->audit_user_id = $session->obj->user_id;
                    $this->orderBy('updated desc');
                    $this->limit($limit);
                    $this->fb_fieldsToRender = array('updated', 'index_id', 
                                                     'table_name');
                    $this->CoopView->recordActions = array('details' => 'Details');
                    print $this->CoopView->simpleTable();
            }

        }

    function fb_display_view(&$co)
        {

            //XXX move this to a session var or somethign. or provide a chooser
            $limit = $_REQUEST['limit'] ? $_REQUEST['limit'] : 20;

            $perm = $co->isPermittedField(NULL);

            $res .= "<h3>An extensive audit trail of the last $limit items of activity on the site.</h3><p>This is still an experimental feature</p>";
            $res .= "<p>Times are in Mountain Time (Phoenix, AZ).</p>";

            // don't show details unless you're privileged.
            // TODO: make thie a hell of a lot more sensible
            $this->fb_recordActions = array();
            $perm >  ACCESS_VIEW &&  
                $this->fb_recordActions['details'] = ACCESS_VIEW;

            $this->orderBy('updated desc');
            $this->limit($limit);

            array_push($this->fb_fieldsToRender, 'table_name');

            $res .= $co->simpleTable();
	 

            $logins = new CoopView(&$co->page, 'session_info', $none);

            $perm >  ACCESS_VIEW &&  
                $logins->obj->fb_recordActions['details'] = ACCESS_VIEW;
            $logins->obj->whereAdd('user_id > 0');
            $logins->obj->orderBy('updated desc');
            $logins->obj->limit($limit);
            $logins->recordActions = array('details' => 'Details');
            $res .= $logins->simpleTable();
            
            return $res;
        }


    function formatChanges(&$co, $val, $key)
        {
            $res = '';
            //return '<pre>' .print_r(unserialize($val),1) . '</pre>';
            $changes = unserialize($val);
            $cho = new CoopObject(&$co->page, $this->table_name, &$nothing);
  
            //PEAR::raiseError('you are trying to show the audit trail for a table that no longer exists. did you rename your tables? did you remember to change all the values in the audit_trail.table_name to match it? bad bad bad.', 666);
            
            //XXX what if this fails?
            $cho->obj->get($this->index_id);
            
            foreach($changes as $field => $change){
                $res .=  $cho->obj->fb_fieldLabels[$field];
                if($cho->isPermittedField($field) >= ACCESS_VIEW){
                    $res .= sprintf(': %s => %s', 
                                    $change['old'], $change['new']);
                } else {
                    $res .= "(Not Permitted)";
                }
            }
            return $res;
        }

}

?>