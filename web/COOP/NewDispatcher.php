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


require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');


//////////////////////////////////////////
/////////////////////// COOP DISPATCHER CLASS
class CoopNewDispatcher
{
	var $page;  				// cached coopPage object

	function CoopNewDispatcher(&$page)
		{
			$this->page = $page;
		}




    function view()
        {
            
            $atd =& new CoopView(&$this->page, 
                                 $this->page->vars['last']['table'], $none);
            //$atd->debugWrap(2);
            
            $res .= '<div><!-- status alert div -->';
            
            $atd2 =& new CoopView(&$this->page, 
                                  $this->page->vars['last']['table'], $none);
            // alert  and/or summary does a find, so i need a separate obj for it
            
            if(is_callable(array($atd2->obj, 'fb_display_summary'))){
                $atd2->page->printDebug('calling callback for summary', 2);
                $res .= $atd2->obj->fb_display_summary(&$atd2);
            }
            if(is_callable(array($atd2->obj, 'fb_display_alert'))){
                $atd2->page->printDebug('calling callback for alert', 2);
                $res .= $atd2->obj->fb_display_alert(&$atd2);
            }
            $res .= '</div><!-- end status alert div -->';
            

            if(is_callable(array($atd->obj, 'fb_display_view'))){
                $this->page->printDebug('calling callback for view', 2);
                return $atd->obj->fb_display_view(&$atd);
            }
            

            //TODO: some variation on the old "perms display" from auth.inc
            //maybe at or top of doc? with editor to change them? ;-)
            
            $res .= $atd->simpleTable();
            
            return $res;
         			
        }



    function add_edit(){

        $this->page->confessArray($this->page->vars, 'vars prior to merge', 4);

        // NOT the coopView above!
        $atdf = new CoopForm(&$this->page, 
                             $this->page->vars['last']['table'], $none); 


        $atdf->build($this->page->mergeRequest());


        // ugly assthrus for my cheap newDispatcher
        $atdf->form->addElement('hidden', 'action', 
                                $this->page->vars['last']['action']); 
        $atdf->form->addElement('hidden', 'table', 
                                $this->page->vars['last']['table']); 

        $atdf->legacyPassThru();

        $atdf->addRequiredFields();

        // XXX THIS CLOBBERS PREVIOUS!
        $this->page->vars['last']['submitvars'] = $atdf->form->getSubmitValues();
        //confessArray($this->page->vars['last'], 'lastHACK');

        if ($atdf->validate()) {
            $res .= "saving...";
            $res .= $atdf->form->process(array(&$atdf, 'process'));
            // 0-based stack
            if($previous =& $this->page->getPreviousStack()){
                $previous['submitvars'][$previous['table'].'-'.$atdf->pk] = 
                    $atdf->id;
                $this->page->confessArray($previous, 'thevars', 4);
            }
            // only go back to view if previous state was 'edit'
            if($this->page->vars['last']['action'] == 'edit'){
                //force it
                $this->page->vars['last']['action'] = 'view';
                // TODO: i can instead, send a POP here with header location!
            } else {
                //SUCCESSFUL, display a new blank entry
                $atdf->page->confessArray($this->page->vars['last'], 
                                          'recursive request before redisplay',
                                          2);
                //XXX this is dumb. 
                $res .= '<p>You may add another below, or click below to go back to viewing</p>';
                $res .=  $atdf->page->selfURL(
                    array('value' => 'View',
                          'inside' => array('action' => 'view',
                                            'table' => $atdf->table)));
            }
            $res .=  $this->page->selfURL(
                array('value' => 'Entry was successful! Click here to continue.'));
            $this->page->buffer($res); // XXX wrong!
            $this->page->popOff(); 
            
            $this->page->headerLocation($this->page->selfURL(
                                        array('par' => false,
                                              'host' => true)));
        } else {
            $res .= $atdf->form->toHTML();
            return $res;
        }
    }


    function details()
        {

            $atd =& new CoopView(&$this->page, $this->page->vars['last']['table'], $none);

            $atd->fullText = true;    // force details to show all
            // MUST DO THIS! FIRST! please find a better way, this sucks
            $atd->obj->{$atd->pk} = $this->page->vars['last']['id'];

            // object-specific override if needed
            if(is_callable(array($atd->obj, 'fb_display_details'))){
                $res .= $atd->obj->fb_display_details(&$atd);
                break;
            }
     

            $id = $this->page->vars['last']['id'];
            $atd->obj->{$atd->pk} = $id;
            $atd->obj->find(true);		//  XXX aack! need this for summary
            $res .= $atd->horizTable();
     
            // try to intelligently find all forward/backlinks
            // or intermediately, adapt findfamily, and pass a list of tables
            // let the code go fish out the path to 'em

            foreach($atd->allLinks() as $table => $ids){
                list($nearid, $farid) = $ids;
                $this->page->printDebug("$atd->table  link for $nearid {$atd->obj->$nearid}  $table", 4);
                $aud =& new CoopView(&$this->page, $table, &$atd);
                $tabs = $aud->obj->table();
                $farwhole = $farid;
                if(!empty($tabs[$farid])){
                    $farwhole = "{$aud->table}.$farid";
                }
                $aud->obj->whereAdd(sprintf('%s = %d', 
                                            $farwhole, $atd->obj->{$nearid}));
                //confessObj($aud, 'aud');
                $aud->debugWrap(5);
                $res .= $aud->simpleTable();
            }


            if(is_array($atd->obj->fb_extraDetails)){
                foreach($atd->obj->fb_extraDetails as $path){
                    // XXX this only handles one-degree-of-separation!
                    list($join, $dest) = explode(':', $path);
                    $co2 =& new CoopObject(&$atd->page, $join, &$atd);
                    $co2->obj->whereAdd(sprintf('%s = %d', 
                                                $atd->pk, 
                                                $id));
                    $real =& new CoopView(&$atd->page, $dest, &$co2);
                    $real->obj->orderBy('school_year desc');
                    $real->obj->joinadd($co2->obj);
                    $res .= $real->simpleTable();
                }
            }

            // standard audit trail, for all details
            $aud =& new CoopView(&$this->page, 'audit_trail', &$atd);
            $aud->obj->table_name = $atd->table;
            $aud->obj->index_id = $id;
            $aud->obj->orderBy('updated desc');
            $res .= $aud->simpleTable();


            if($this->page->vars['last']['realm']){
                $realm =& new CoopView(&$this->page, 'realms', &$atd);
                $realm->obj->get($this->page->vars['last']['realm']);
                $res .= $this->page->selfURL(
                    array('value' => 
                          "Click here for complete audit trail of all {$realm->obj->short_description}",
                          'inside' => array('table' => 'audit_trail',
                                            // XXX realm_id superflouos, using last!
                                            'realm_id' => $this->page->vars['last']['realm'])));
            }

            $res .= $this->page->selfURL(
                array('value' => 'Click here for detailed view of Permissions',
                      'inside' => array('table' => $atd->table,
                                        'action' => 'perms')));

            return $res;

        }


function confirmDelete()
        {

            if($res = $this->bruteForceDeleteCheck()){
                return $res;
            }
            
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
            
            $res .= $atdf->form->toHTML();

            return $res;
        }

    function delete()
        {
            // hack , but it works. why reinvent the wheel?
            $atdf = new CoopForm(&$this->page, 
                                 $this->page->vars['last']['table'], $none); 
            $atdf->build($this->page->mergeRequest());
            ;
            $atdf->obj->delete();

            // IMPORTANT! i'm done deleting. set the next action
            $this->page->vars['last']['action'] = 'view';

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
                    sprintf('confirmdelete link checking %s => %s [%d]', 
                            $vatd->table, $checktab, $id), 
                    4);
                $check =& new CoopView(&$vatd->page, $checktab, &$vatd);
                $check->debugWrap(7);
                $check->obj->{$vatd->pk} = $id;
                $found = $check->obj->find();
                if($found){
                    $totalfound += $found;
                    $res .= $check->simpleTable(false);
                }
            }
            
            if($totalfound){
                $restop = $vatd->horizTable();
                return $restop . '<p class="error">YOU CANNOT DELETE THIS RECORD because the records below depend on it. Fix these first.</p>' .  $res;
                
            }
            
            return false;
        }




function dispatch()
{

    // cheap newDispatcher
    //confessArray($this->page->vars['last'],'req');
    switch($this->page->vars['last']['action']){

    case 'new':
    case 'add':					//  for OLD menu system
    case 'edit':
        return $this->add_edit();
        break;
 
    case 'perms':
        $atd =& new CoopView(&$this->page, 
                             $this->page->vars['last']['table'], $none);
        return $atd->showPerms();
        break;

    case 'details':
        return $this->details();
        break;

    case 'confirmdelete':
        return $this->confirmdelete();
        break;

    case 'delete':
        return $this->delete();
        break;

    case 'view':
    default:
        return $this->view();
        break;
    }

} // end dispatch




} // END NEW COOP DISPATCHER CLASS


////KEEP EVERTHANG BELOW

?>