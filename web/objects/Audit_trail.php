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
		'table_name' => 'Table',
		'index_id' => 'Unique ID',
		'audit_user_id' => 'Edited By',
		'updated' => 'Edited On',
        'details' => 'What changed'
		);
	var $fb_fieldsToRender = array ('audit_user_id', 'updated', 'details');
    var $fb_recordActions = array();
    var $fb_viewActions = array();
    var $fb_displayCallbacks = array('details' => 'formatChanges',
                                     'table_name' => 'useLabel');

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


        }

    function fb_display_view(&$co)
        {
            $res = '';

            $res .= "<p>Times are in Mountain Time (Phoenix, AZ).</p>";
                        
            /// THE CHOOSER FORM
            $syform =& new HTML_QuickForm('auditreport', false, false, 
                                          false, false, true);
            $el =& $syform->addElement('text', 'limit', 'Records to show', 
                                       '20',
                                       array('onchange' =>
                                             'javascript:submitForm()'));

            $sel =& $syform->addElement('select', 'realm_id', 'Realm', 
                                       $nothing,
                                       array('onchange' =>
                                             'javascript:submitForm()'));
            $sel->addOption('ALL', '%');
            $realms =& new CoopView(&$co->page, 'realms', &$co);
            $realms->find(true); // go get, including any constraints/sorts
            $sel->loadDbResult($realms->obj->getDatabaseResult(), 
                               'short_description', 'realm_id');

            if($sid = thruAuthCore($co->page->auth)){
                $syform->addElement('hidden', 'coop', $sid); 
            }
            $syform->addElement('hidden', 'table', $co->table); 


            // need change button?
            $syform->addElement('submit', 'savebutton', 'Change');
                
            
            $syform->setDefaults(array('limit' =>20, 
                                       'realm_id' => '0'));
            

            $foo = $sel->getValue();
            $realm_id = $foo[0];

            $res .= $syform->toHTML();
            
            $limit = $el->getValue();


            /// CONSTRAIN TO REALM
            $hack =& $this->factory('realms');
            $hack->get($realm_id);

            $tb =& $this->factory('table_permissions');
            $tb->realm_id = $realm_id;
            $tb->find();
            $tables = array();
            while($tb->fetch()){
                $tables[] = "'{$tb->table_name}'";
            }
            count($tables) && $this->whereAdd(sprintf('table_name in (%s)',
                                    implode(',', $tables)));


            /// DEAL WITH SUMMARY
            array_push($this->fb_fieldsToRender, 'summary');
            // XXX ugly hack to prepend it to the beginning of the array
            $this->fb_fieldLabels = array_merge(
                array('summary' => 'Record Edited'),
                $this->fb_fieldLabels);
            $this->selectAdd('index_id  as summary');
            $this->fb_displayCallbacks['summary'] = 'summarizeLink';



            /// FINALLY! show the damned thing

            $perm = $co->isPermittedField(NULL);


            // don't show details unless you're privileged.
            // TODO: make thie a hell of a lot more sensible
            $this->fb_recordActions = array();
            $perm >  ACCESS_VIEW &&  
                $this->fb_recordActions['details'] = ACCESS_VIEW;

            $this->orderBy('updated desc');
            $this->limit($limit);

            array_push($this->fb_fieldsToRender, 'table_name');

            $res .= $co->simpleTable();
	 

            return $res;
        }


    function formatChanges(&$co, $val, $key)
        {
            // XXX have to do this here, this is an autogenerated file
            
            
            $res = '';
            
            if(!$val){
                return 'No details saved';
            }

            //return '<pre>' .print_r(unserialize($val),1) . '</pre>';
            $changes = unserialize($val);
            $cho = new CoopObject(&$co->page, $this->table_name, &$nothing);
  
            //PEAR::raiseError('you are trying to show the audit trail for a table that no longer exists. did you rename your tables? did you remember to change all the values in the audit_trail.table_name to match it? bad bad bad.', 666);
            
            //XXX what if this fails?
            $cho->obj->get($this->index_id);
            
            foreach($changes as $field => $change){
                
                $cho->obj->$field = $change['old'];
                $oldformatted = $cho->checkLinkField($field, 
                                                     $cho->obj->$field);

                $cho->obj->$field = $change['new'];
                $newformatted = $cho->checkLinkField($field, 
                                                     $cho->obj->$field);

                // ok, so this is a bit of a duplication of coopview.
                $res .=  empty($cho->obj->fb_fieldLabels[$field]) ?
                    $field : $cho->obj->fb_fieldLabels[$field];
                
                // finally, SHOW the damned thing
                if($cho->isPermittedField($field) >= ACCESS_VIEW){
                    if(is_array($cho->obj->fb_textFields) &&
                       in_array($field, $cho->obj->fb_textFields))
                    {
                        
                        require_once('Text/Diff.php');
                        require_once('Text/Diff/Renderer.php');
                        require_once('Text/Diff/Renderer/inline.php');
                        
                        $diff =& new Text_Diff(
                            explode("\n",$oldformatted), 
                            explode("\n", $newformatted));
                        $rend =& new Text_Diff_Renderer_inline();
                        //$co->page->confessArray($rend->getParams(), 'inline params', 4);
                        $res .= nl2br($rend->render($diff));
                        
                    } else {
                        $res .= sprintf(': %s changed to %s', 
                                        $oldformatted, $newformatted);
                    }
                } else {
                    $res .= " (Not Permitted) ";
                }
                $res .= '<br />';
            }
            return $res;
        }




    function useLabel(&$co, $val, $key)
        {
            //WARNING this can really suck if you change table names!!
            // you must run the update if needed
            $sub =& $this->factory($val);
            return $sub->fb_formHeaderText;
            
        }

    function summarizeLink(&$co, $val, $key)
        {
            //WARNING this can really suck if you change table names!!
            // you must run the update if needed
            $sub =& new CoopObject(&$co->page, $this->table_name, &$co);
            $sub->obj->get($val);
            if($sub->isPermittedField() < ACCESS_VIEW){
                return '(Not permitted)';
            }
            return $sub->concatLinkFields();
        }


}

?>