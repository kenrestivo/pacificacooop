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
	var $fb_shortHeader =  'Audit';
	var $fb_linkDisplayFields = array();
	var $fb_fieldLabels = array (
		'table_name' => 'Name of Table',
		'index_id' => 'Unique ID',
		'audit_user_id' => 'Edited By',
		'updated' => 'Edited On'
		);
	var $fb_fieldsToRender = array ('audit_user_id', 'updated');

    //XXX details surfing is broken, this request shit. force off.
//     var $fb_recordActions = array();
//     var $fb_viewActions = array();

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

    function fb_display_view()
        {

            //XXX move this to a session var or somethign. or provide a chooser
            $limit = $_REQUEST['limit'] ? $_REQUEST['limit'] : 20;

            $perm = $this->CoopView->isPermittedField();

            $res .= "<h3>An extensive audit trail of the last $limit items of activity on the site.</h3><p>This is still an experimental feature</p>";
            $res .= "<p>Times are in Mountain Time (Phoenix, AZ).</p>";
	 
            $perm <  ACCESS_VIEW && $this->audit_user_id = 
                $this->CoopView->page->userStruct['uid'];
            $this->orderBy('updated desc');
            $this->limit($limit);
            array_push($this->fb_fieldsToRender, 'table_name');
            $this->CoopView->recordActions = array('details' => 'Details');
            $res .= $this->CoopView->simpleTable();
	 

            $logins = new CoopView(&$this->CoopView->page, 'session_info', $none);
            $perm <  ACCESS_VIEW && $logins->obj->user_id = 
                $this->CoopView->page->userStruct['uid'];
            $logins->obj->whereAdd('user_id > 0');
            $logins->obj->orderBy('updated desc');
            $logins->obj->limit($limit);
            $logins->recordActions = array('details' => 'Details');
            $res .= $logins->simpleTable();
            
            return $res;
        }


}

?>