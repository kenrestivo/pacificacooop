<?php
/**
 * Table Definition for audit_trail
 */
require_once 'DB/DataObject.php';

class Audit_trail extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'audit_trail';                     // table name
    var $audit_trail_id;                  // int(32)  not_null primary_key unique_key auto_increment
    var $table_name;                      // string(255)  
    var $index_id;                        // int(32)  
    var $audit_user_id;                   // int(32)  
    var $updated;                         // datetime(19)  not_null binary
    var $details;                         // blob(16777215)  blob
    var $email_sent;                      // int(1)  

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
        'details' => 'What changed',
        'email_sent' => 'Email notification'
		);
	var $preDefOrder = array (
		'table_name' ,
		'index_id' ,
		'audit_user_id', 
		'updated' ,
        'details' ,
        'email_sent' 
		);
	var $fb_fieldsToRender = array ('audit_user_id', 'updated', 
                                    'email_sent', 'details');
    var $fb_recordActions = array();
    var $fb_viewActions = array();
    var $fb_displayCallbacks = array('details' => 'formatChanges',
                                     'table_name' => 'useLabel',
                                     'email_sent' => 'sendNow');
                                     

    function BORKENfb_display_details(&$co)
        {
            //XXX move this to 'last'
            $limit = $_REQUEST['limit'] ? $_REQUEST['limit'] : 20;
	 
            $session = new CoopView(&$co->page, 'session_info', $none);

            // this is an interesting pseudo sub-dispatcher
            if($_REQUEST[$co->prependTable($co->pk)]){
                $this->{$co->pk} = 
                    $_REQUEST[$co->prependTable(
                                  $co->pk)];
                    //array_push($this->fb_fieldsToRender, 'table_name', 'index_id');
                    $this->find(true);
                    //print $co->horizTable();
			 
                    // basically the stuff in genericdetails.
                    $thing = new CoopView(&$co->page, $this->table_name, &$nothing);
                    $thing->obj->{$thing->pk} = $this->index_id;
                    print $thing->horizTable();
			 
                    // standard audit trail, for all details
                    $aud =& new CoopView(&$co->page, 'audit_trail', &$thing);
                    $aud->obj->table_name = $this->table_name;
                    $aud->obj->index_id = $this->index_id;
                    $aud->obj->orderBy('updated desc');
                    print $aud->simpleTable(true, true);
            }


        }

    function fb_display_view(&$co)
        {
            $res = '';

            $res .= "<p>Times are in Mountain Time (Phoenix, AZ).</p>";
                  

            /// THE CHOOSER FORM
            $syform =& new HTML_QuickForm('auditreport', false, false, 
                                          false, false, true);
            $syform->removeAttribute('name');
            $el =& $syform->addElement('text', 'limit', 'Records to show', 
                                       '20',
                                       array('onchange' =>
                                             'this.form.submit()'));

            $sel =& $syform->addElement('select', 'realm_id', 'Realm', 
                                       $nothing,
                                       array('onchange' =>
                                             'this.form.submit()'));
            $sel->addOption('ALL', '0');
            $realms =& new CoopView(&$co->page, 'realms', &$co);
            $realms->find(true); // go get, including any constraints/sorts
            $sel->loadDbResult($realms->obj->getDatabaseResult(), 
                               'short_description', 'realm_id');

            // NOTE! THIS IS STIL THE OLDSTYLE!

            if($sid = thruAuthCore($co->page->auth)){
                $syform->addElement('hidden', 'coop', $sid); 
            }


            // need change button?
            $syform->addElement('submit', 'savebutton', 'Change');
                

            /// XXX NASTY NASTY NASTY HACK!
            $url = parse_url($_SERVER['HTTP_REFERER']);
            $base = basename($url['path']);

            //COOL! this is the first place i am using vars->last
            $syform->setDefaults(
                array('limit' =>20, 
                      'realm_id' => $base == 'index.php' ? 0 : 
                      $co->page->vars['last']['realm'] ));
            

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

            $this->fb_fieldLabels['summary'] = 'Record Edited';
            array_unshift($this->preDefOrder, 'summary');

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

            $res .= $co->simpleTable(true, true);
	 

            return $res;
        }


    function formatChanges(&$co, $val, $key)
        {
            // XXX have to do this here, this is an autogenerated file
            require_once('Text/Diff.php');
            require_once('Text/Diff/Renderer.php');
            require_once('Text/Diff/Renderer/inline.php');
            require_once('lib/class.html2text.inc');

            
            $res = '';
            
            if(!$val){
                $newaud = $this->factory($co->table);
                $newaud->table_name = $this->table_name;
                $newaud->index_id = $this->index_id;
                $newaud->orderBy('updated desc');
                $newaud->limit(1);
                $newaud->find(true);
                if($newaud->{$co->pk} == $this->{$co->pk}){
                    return 'New record added';
                }
                return 'No details saved';
            }

            //return '<pre>' .print_r(unserialize($val),1) . '</pre>';
            $changes = unserialize($val);
            $cho = new CoopObject(&$co->page, $this->table_name, &$nothing);
  
            //PEAR::raiseError('you are trying to show the audit trail for a table that no longer exists. did you rename your tables? did you remember to change all the values in the audit_trail.table_name to match it? bad bad bad.', 666);
            
            //XXX what if this fails?
            $cho->obj->get($this->index_id);
            $h2t =& new html2text();
            $h2t->width = 9999;
                
            
            foreach($changes as $field => $change){
                
                // skip cached shorttext and any other privates
                if(preg_match('/^_/', $field)){
                    continue;
                }

                // XXX hack! stuffing these into the object
                // so that checklinkfield is happy
                $cho->obj->$field = $change['old']; 
                $h2t->set_html($cho->checkLinkField($field, 
                                                     $cho->obj->$field));
                $oldformatted = $h2t->get_text();


                $cho->obj->$field = $change['new']; 
                $h2t->set_html($cho->checkLinkField($field, 
                                                     $cho->obj->$field));
                $newformatted = $h2t->get_text();

                // ok, so this is a bit of a duplication of coopview.
                $res .=  empty($cho->obj->fb_fieldLabels[$field]) ?
                    $field : $cho->obj->fb_fieldLabels[$field];
                $res .= ": ";
                
                if($cho->isPermittedField($field) < ACCESS_VIEW){
                    $res .= " (Not Permitted) ";
                    continue;
                }                    
                
                if(!strstr($co->page->content_type, 'html')){  
                    // for the emails, because the underline and
                    // strikethroughs don't  convert to text
                    $res .= sprintf(" '%s' changed to '%s'\n", 
                                    $oldformatted, $newformatted);
                } else {
                    $diff =& new Text_Diff(
                        explode("\n",$oldformatted), 
                        explode("\n", $newformatted));
                    $rend =& new Text_Diff_Renderer_inline();
                    //$co->page->confessArray($rend->getParams(), 'inline params', 4);
                    $res .= nl2br($rend->render($diff));
                }
                $res .= strstr($co->page->content_type, 'html') ? '<br />': "\n" ;
            }
            //TODO: do a textto html here instead?!
            // based on content/type? or always?
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
            return htmlentities(unHTML(strip_tags($sub->concatLinkFields())));
        }



    function sendNow(&$co, $val, $key)
        {
            if($val){
                return 'Done';
            } 
            
            $res = "";

            $res .= $co->page->jsRequireOnce('lib/MochiKit/MochiKit.js',
                                     'INCLUDE_MOCHIKIT');

            
            $res .= $co->page->jsRequireOnce('lib/send_email.js', 
                                   'SENDEMAIL_EXISTS');

            $res .= sprintf(
                '<a href="" onclick="return sendEmailNotice(this,%d)">Send</a>', 
                $this->{$co->pk});
          
          return $res;
      }


}

?>