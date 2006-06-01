<?php 

//$Id$

/*
	Copyright (C) 2005  ken restivo <ken@restivo.org>
	 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details. 
	
	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


require_once('COOP/Page.php');
require_once('COOP/View.php');
require_once('COOP/Form.php');


//////////////////////////////////////////
/////////////////////// NEW COOP DISPATCHER CLASS
class CoopNewDispatcher
{
	var $page;  				// cached coopPage object


	function CoopNewDispatcher(&$page)
		{
			$this->page =& $page;
		}


    // returns the atd for the view, if any
    function &handleStack()
        {
            // save a copy of this stuff before merging
            // XXX ok what about reports? no table for those puppies.
            if(!empty($_REQUEST['table'])){
                $atd =& new CoopView(&$this->page, $_REQUEST['table'], $none);
                $formatted = array('table'=>$_REQUEST['table'], 
                                   'action' =>$_REQUEST['action'] ? 
                                   $_REQUEST['action'] : 
                                   'view', 
                                   'pop' => @$_REQUEST['pop'], 
                                   'id' =>@$_REQUEST[$atd->prependTable($atd->pk)],
                                   'realm' => $_REQUEST['realm'] ? $_REQUEST['realm'] : 
                                   $this->page->vars['last']['realm']);
            }
            // push is set by recordbuttons, actionbuttons, and some forms:
            // anyone who wants to save its place and then return to it
            if(isset($_REQUEST['push'])){
                $this->page->printDebug('PUSHING onto the stack!', 1);
                $this->page->vars['stack'][] = $this->page->vars['last'];
            } 

            // save the request as 'last'-- which is current
            // ALWAYS use formatted as last.... if it exists, that is
            // it won't exist in cases where i'm coming back from a header location
            if(!empty($formatted)){
                $this->page->vars['last'] =  $formatted;
            }

            // critical to do this here. 
            $prev = $this->page->popOff();
 
            // if no dispacinformation was passed, redirect to members homepage
            // XXX what to do in case of report? there'll be no table, i expect
            if(!$this->page->vars['last']['table']){
                $this->page->headerLocation(
                    $this->page->selfURL(
                        array('par' => false,
                              'base' => 'index.php',
                              // remember bug in selfurl: must have something inside
                              'inside' => array('redirect' => 'yes'),
                              'host' => true)));
            }

            return $atd; 
        }



    function view()
        {
            
            $res = '';

            $atd =& new CoopView(&$this->page, 
                                 $this->page->vars['last']['table'], $none);
            //$atd->debugWrap(2);
            
            $res .= '<div id="status_alert">';
            
            // the status of the last action
            // TODO: put this in a distinctive statusbar div, not in here
            $res .= '<div id="status">' . $this->page->getStatus() . '</div>';

            $atd2 =& new CoopView(&$this->page, 
                                  $this->page->vars['last']['table'], $none);
            // alert  and/or summary does a find, so i need a separate obj for it
            
            $res .= $atd2->getSummary();

            if($alert = $atd2->getAlert()){
                $res .= $alert;
            }
            $res .= '</div>';


            //TODO: some variation on the old "perms display" from auth.inc
            //maybe at or top of doc? with editor to change them? ;-)
            
            $res .= $atd->getInstructions('view');
            

            if(is_callable(array($atd->obj, 'fb_display_view'))){
                $this->page->printDebug('calling callback for view', 2);
                return $res . $atd->obj->fb_display_view(&$atd);
            }
            
            
            // use the built-in find. AND FORCE ALWAYS TO SHOW!
            $res .= $atd->simpleTable(true, true);
            
            return $res;
         			
        }


    /// this is the ugliest code i've ever been embarrassed to have written
    /// and yet, this convoluted mess is the core of the entire damned website.
    /// big fear.
    function add_edit()
        {
            $res = '';

            // NOT the coopView above!
            $atdf = new CoopForm(&$this->page, 
                                 $this->page->vars['last']['table'], $none); 


            $atdf->build($this->page->mergeRequest());


            // ugly assthrus for my cheap newDispatcher
            $atdf->form->addElement('hidden', 'action', 
                                    $this->page->vars['last']['action']); 
            $atdf->form->addElement('hidden', 'table', 
                                    $this->page->vars['last']['table']); 

            // XXX do i really need this anymore? it gets in the way of leads
            $atdf->legacyPassThru();

            $atdf->addRequiredFields();

            // XXX THIS CLOBBERS WHATEVER WAS THERE!
            // don't i have an array merge function somewhere?
            // also, shouldn't i use exportValues(), to get only thos in the QF?
            $this->page->vars['last']['submitvars'] = $atdf->form->getSubmitValues();
            
            // this is crucial for, well, everything.
            $prev =& $this->page->getPreviousStack();


            //try to make it more userfriendly by specifying commit action
            if($atdf->form->elementExists('savebutton')){
                $sbtn =& $atdf->form->getElement('savebutton');
                if($prev && 
                   ($prev['action'] == 'add' || $prev['action'] == 'edit'))
                {
                    $sbtn->setValue('Continue>>');
                } else if($this->page->vars['last']['action'] == 'add'){
                    $sbtn->setValue('Add');
                } else {
                    $sbtn->setValue('Save Changes');
                }
            }

            if ($atdf->validate()) 
            {

                $this->page->vars['last']['result'] = 
                    $atdf->form->process(array(&$atdf, 'process'));

                // put my saved ID back on the stack for later popping!
                if($prev){
                    //use whatever is in the LINKED table
                    $backid = $atdf->backlinks[$prev['table']];
                    $backid = $backid ? $backid : $atdf->pk;
                    $prev['submitvars'][$prev['table'].'-'.$backid] = 
                        $atdf->id;
                }
                // force back to view if previous state was 'edit'
                // or previous table is DIFFERENT (XXX NASTY hack!)
                if($this->page->vars['last']['action'] != 'add' ||
                   (!empty($prev['table']) && 
                    $this->page->vars['last']['table'] != $prev['table']))
                {
                    $this->page->vars['last']['pop'] = $atdf->table; 
                } else if ($this->page->vars['last']['action'] == 'add'){
                    $this->page->vars['last']['result'] .= 
                        ' Add another below if you like.';
                }
            

                $this->page->headerLocation(
                    $this->page->selfURL(array('par' => false,
                                               'host' => true)));
            } else {
                // didn't validate. there are errors or it's not done (push/pop)
                if($atdf->isSubmitted()){
                    $res .= $this->page->vars['last']['result'] = 
                        sprintf('<p class="error">%s %s has errors. Please correct.</p>',
                                $atdf->actionnames[$this->page->vars['last']['action']],
                                $atdf->obj->fb_formHeaderText);
                }


				//if previous state was was add,
				// and something popped to get me here,
				// something was inserted before i got here
				// so freeze just that field that was inserted
                if($this->page->vars['prev']['action'] == 'add' &&
                   !empty($this->page->vars['prev']['pop']))
                {
                    // i'm assuming it's always gonna be a forwardlink!
                    $localfield = 
                        $atdf->getLinkField($this->page->vars['prev']['table']);
                    if($atdf->form->elementExists(
                           $atdf->prependTable($localfield)))
                    {
                        $fr =& $atdf->form->getElement(
                            $atdf->prependTable($localfield));
                        $fr->freeze();
                        $this->page->confessArray(
                            $this->page->vars, 
                            "$localfield vars just before tohtml",
                                                  4);
                    }
                } else {
                    // no pop, so OK to show status
                    $res .= '<p>' . $this->page->getStatus() . '</p>';
                }
                


                // last thing before i show it: instructions
                // WARNIGN! FRAGILE! assume action hasn't been mangled!
                $res .= $atdf->getInstructions(
                    $this->page->vars['last']['action']);

                
                /// finally, show the before/after hacks
                /// (XXX these suck. use templates or CSS instead!)
                /// and, actually display the damned form! yippie!
                if(is_callable(array($atdf->obj, 'beforeForm'))){
                    $this->page->printDebug('newdispatcher calling before form', 3);
                    $res .= $atdf->obj->beforeForm(&$atdf);
                }

                $res .= $atdf->form->toHTML();

                if(is_callable(array($atdf->obj, 'afterForm'))){
                    $this->page->printDebug('newdispatcher calling after form', 3);
                    $res .= $atdf->obj->afterForm(&$atdf);
                }

                //confessObj($atdf->form, 'the form structure');
                return $res;
            }
        }


 

    function details()
        {

            $res .= '<p>' . $this->page->getStatus() . '</p>';

            $atd =& new CoopView(&$this->page, 
                                 $this->page->vars['last']['table'], 
                                 $none);

            $atd->fullText = true;    // force details to show all
            // MUST DO THIS! FIRST! please find a better way, this sucks
            $id = $atd->obj->{$atd->pk} = $this->page->vars['last']['id'];

            // object-specific override if needed
            if(is_callable(array($atd->obj, 'fb_display_details'))){
                $res .= $atd->obj->fb_display_details(&$atd);
				return $res;
            }

            $res .= $atd->getInstructions('details');
     

            $atd->obj->get($id);
            $res .= $atd->horizTable(false);
     

            $res .= $atd->showLinkDetails();

            // standard audit trail, for all details
            //TODO: eventually move this to coopview?
            $aud =& new CoopView(&$this->page, 'audit_trail', &$atd);
            $aud->obj->table_name = $atd->table;
            $aud->obj->index_id = $id;
            $aud->obj->orderBy('updated desc');
            $res .= $aud->simpleTable(true,true);


            /// XXX TOTALLY BROKEN!! and i haven't time or patience to fix it
//             if($this->page->vars['last']['realm']){
//                 $realm =& new CoopView(&$this->page, 'realms', &$atd);
//                 $realm->obj->get($this->page->vars['last']['realm']);
//                 $res .= $this->page->selfURL(
//                     array('value' => 
//                           "Click here for complete audit trail of all {$realm->obj->short_description}",
//                           'inside' => array('table' => 'audit_trail',
//                                             // XXX realm_id superflouos, using last!
//                                             'realm_id' => $this->page->vars['last']['realm'])));
//             }

            $res .= $this->page->selfURL(
                array('value' => 'Click here for detailed view of Permissions',
                      'inside' => array('table' => $atd->table,
                                        'action' => 'perms')));

            return $res;

        }


function confirmdelete()
        {
            $res = '';
            //TODO: put on a permissions condom here
            $res .= '<p>' . $this->page->getStatus() . '</p>';


            if($res = $this->bruteForceDeleteCheck()){
                return $res;
            }

            // am i sure i want to use coopform here?
            // why not horiztable instead?
            // the only buttons/action in need here are delete/cancel
            // could do those as links (GET) not form buttons (POST), no?

            $res .= "<p>Are you sure you wish to delete this? Click 'Delete' or 'Cancel' to go back.</p>";	 
            $atdf = new CoopForm(&$this->page, 
                                 $this->page->vars['last']['table'], $none); 
            $atdf->build($this->page->mergeRequest());
            
            $atdf->form->addElement('hidden', 'action', 'delete'); 
            $atdf->form->addElement('hidden', 'table', 
                                    $this->page->vars['last']['table']); 
            
            $atdf->legacyPassThru();
            
            $atdf->addRequiredFields();
            
            
            // change the save button and action to delete
            $el =& $atdf->form->getElement('savebutton');
            $el->setValue('Delete');
            
            
            //TODO and add a cancel button
            //$atdf->form->addElement('button', 'cancelbutton', 'Cancel');
            
            $atdf->form->freeze();

            $res .= $atdf->getInstructions('delete');
            
            $res .= $atdf->form->toHTML();

            return $res;
        }

    function delete()
        {

            //TODO: put on a permissions condom here

            // hack , but it works. why reinvent the wheel?
            $atdf = new CoopForm(&$this->page, 
                                 $this->page->vars['last']['table'], $none); 
            $atdf->build($this->page->mergeRequest());
            
            // TODO: override this in coopform, and saveaudit on deletes!
            $atdf->obj->delete();

            // to display success message
            $this->page->vars['last']['result'] = 
                sprintf('Successfully deleted %s entry.',
                        $atdf->obj->fb_formHeaderText); 
            //XXX is this the right way to do this?
            $this->page->vars['last']['pop'] = $atdf->table; 

            $this->page->headerLocation($this->page->selfURL(
                                        array('par' => false,
                                              'host' => true)));
        }


        //TODO: move this to coopview?
        //the presence of get, is problematic. use the coopview find? or get?
    function bruteForceDeleteCheck()
        {
            global $_DB_DATAOBJECT;
            // go get em
            
            $vatd =& new CoopView(&$this->page, 
                                  $this->page->vars['last']['table'], $none);
            $id = $this->page->vars['last']['id'];
            //XXX what if a bad id gets passed in. this SCARES me
            $vatd->obj->get($id); //  XXX aack! need this for summary
            
            
            //NOTE! i do *not* use backlinks/forwardlinks here
            //because some of these fields may not show up as actual links
            //i.e. if they're not PK's! but i still want to prevent orphans
            $links =& $_DB_DATAOBJECT['LINKS'][$vatd->obj->database()];
            foreach($links as $table=> $link){
                foreach($link as $nearcol => $farpair){
                    if($vatd->pk == $nearcol && $table != $vatd->table){
                        $checkme[] = $table;
                    }
                    list($fartab, $farcol) = explode(':', $farpair);
                    if($vatd->pk == $farcol && $fartab != $vatd->table){
                        $checkme[] = $fartab;
                    }
                }
            }
            if(!is_array($checkme)){
                return false;
            }
            $checkme = array_unique($checkme);
            // now check 'em
            foreach($checkme as $checktab){
                $vatd->page->printDebug(
                    sprintf('CoopNewDispatcher::bruteForceDeleteCheck(%s)  %s [%d]', 
                            $vatd->table, $checktab, $id), 
                    2);
                $check =& new CoopView(&$vatd->page, $checktab, &$vatd);
                $check->debugWrap(7);
                $check->obj->{$vatd->pk} = $id;
                $found = $check->obj->find();
                if($found){
                    $totalfound += $found;
                    $this->page->printDebug("CoopNewDispatcher::bruteForce({$this->table}) $id found $found {$check->table}s, but not displaying it because i suck", 
                                            2);
                    $subtable = $check->simpleTable(false,false);
                    if(!$subtable){
                        // there are tables we don't have perms to view
                        // XXX this is a nasty hack. a punt really.
                        // instead show the _join records, or an editor
                        $res .= sprintf(
                            '<p>Links for %s usually can be removed by %s the %s record above, or one of the records below.</p>', 
                            $check->title(),
                            $this->page->selfURL(
                                array('value' => 'editing',
                                      'par' => false,
                                      'inside' => array('push' => $vatd->table,
                                                        'table' => $vatd->table,
                                                        'action' => 'edit',
                                                        $vatd->prependTable($vatd->pk) => $id))),
                            $vatd->title());
                        /// XXX totally borken
                        while($check->obj->fetch()){
                            $res .= $check->showLinkDetails();
                        }
                    }
                    $res .= $subtable;
                }
            }
            
            if($totalfound > 0){
                $restop = $vatd->horizTable(false);
                return $restop . '<p class="error">YOU CANNOT DELETE THIS RECORD because the records below depend on it. Fix these first.</p>' .  $res;
                
            }
            
            return false;
        }


function perms()
        {
            $atd =& new CoopView(&$this->page, 
                                 $this->page->vars['last']['table'], $none);
            return $atd->showPerms();
        
        }

function dispatch()
{

    if(empty($this->page->auth['uid'])){
        //with templates, it's entirely possible to have a blank here
        $this->page->printDebug('dispatcher called with no auth! scramming');
        return;
    }

    //confessArray($this->page->vars['last'],'req');

    $funcname =  $this->page->vars['last']['action'];

    $this->page->printDebug("CoopNewDispatcher::dispatch() going to $funcname");

    // new add edit are all add_edit
    // dammit. 'new' is a keyword.
    if(in_array($funcname, array('new', 'add', 'edit'))){
        return $this->add_edit();
    }


    // make this subclassable! dispatch to internal func
    if(is_callable(array($this, $funcname)) && strpos($funcname, '_') != 1){
        return call_user_func(array($this, $funcname));
    }
   
    // the default
    return $this->view();
            
} // end dispatch



} // END NEW COOP DISPATCHER CLASS


////KEEP EVERTHANG BELOW

?>