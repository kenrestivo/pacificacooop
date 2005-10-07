<?php

//$Id$

//$debug = -1;


require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');


PEAR::setErrorHandling(PEAR_ERROR_PRINT);


$cp = new coopPage( $debug);
print $cp->pageTop();


$menu =& new CoopMenu(&$cp);
print $menu->topNavigation();





function popOff(&$cp)
{
    if(isset($cp->vars['stack']) && count($cp->vars['stack'])){
        $cp->confessArray($cp->vars, 'popping off of this stack', 1);
        $cp->vars['last'] = array_pop($cp->vars['stack']);  
    }
}



//TODO: move this to coopobject? coopview?
function bruteForceDeleteCheck(&$cp)
{
    global $_DB_DATAOBJECT;
    // go get em

    $vatd =& new CoopView(&$cp, $cp->vars['last']['table'], $none);
    $id = $cp->vars['last']['id'];
    $vatd->obj->{$vatd->pk} = $id;
    $vatd->obj->find(true);		//  XXX aack! need this for summary
    
    
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




function genericView(&$cp)
{

    $atd =& new CoopView(&$cp, $cp->vars['last']['table'], $none);
    //$atd->debugWrap(2);

    print '<div><!-- status alert div -->';

    $atd2 =& new CoopView(&$cp, $cp->vars['last']['table'], $none);
    // alert  and/or summary does a find, so i need a separate obj for it

    if(is_callable(array($atd2->obj, 'fb_display_summary'))){
        $atd2->page->printDebug('calling callback for summary', 2);
        print $atd2->obj->fb_display_summary(&$atd2);
    }
    if(is_callable(array($atd2->obj, 'fb_display_alert'))){
        $atd2->page->printDebug('calling callback for alert', 2);
        print $atd2->obj->fb_display_alert(&$atd2);
    }
    print '</div><!-- end status alert div -->';
    

    if(is_callable(array($atd->obj, 'fb_display_view'))){
        $cp->printDebug('calling callback for view', 2);
        return $atd->obj->fb_display_view(&$atd);
    }
    


    //TODO: some variation on the old "perms display" from auth.inc
    //maybe at or top of doc? with editor to change them? ;-)

     $res .= $atd->simpleTable();

    return $res;

			
}

function formaggio(&$cp){

    $cp->confessArray($cp->vars, 'vars prior to merge', 4);

	 // NOT the coopView above!
	 $atdf = new CoopForm(&$cp, $cp->vars['last']['table'], $none); 


	 $atdf->build(array_merge_recursive($_REQUEST, 
                                        $cp->vars['last']['submitvars']));


	 // ugly assthrus for my cheap newDispatcher
	 $atdf->form->addElement('hidden', 'action', $cp->vars['last']['action']); 
	 $atdf->form->addElement('hidden', 'table', $cp->vars['last']['table']); 

	 $atdf->legacyPassThru();

	 $atdf->addRequiredFields();

     // XXX THIS CLOBBERS PREVIOUS!
	 $cp->vars['last']['submitvars'] = $atdf->form->getSubmitValues();
     //confessArray($cp->vars['last'], 'lastHACK');

	 if ($atdf->validate()) {
		 print "saving...";
		 print $atdf->form->process(array(&$atdf, 'process'));
         // 0-based stack
         $previous =& $cp->getPreviousStack();
         $previous['submitvars'][$previous['table'].'-'.$atdf->pk] = $atdf->id;
         $cp->confessArray($previous, 'thevars', 4);
         // only go back to view if previous state was 'edit'
         if($cp->vars['last']['action'] == 'edit'){
             //force it
             $cp->vars['last']['action'] = 'view';
             popOff(&$cp);
             newDispatcher(&$cp);
         } else {
             //SUCCESSFUL, display a new blank entry
             $atdf->page->confessArray($cp->vars['last'], 
                                       'recursive request before redisplay',
                                       2);
             //XXX this is dumb. 
             print '<p>You may add another below, or click below to go back to viewing</p>';
             print  $atdf->page->selfURL(
                 array('value' => 'View',
                       'inside' => array('action' => 'view',
                                         'table' => $atdf->table)));
             
             // XXX bug lurking here. if i want to keep the seeded ID
             // from the page->vars->last, i got problems here.
             popOff(&$cp); 
             newDispatcher(&$cp);
         }
	 } else {
		 print $atdf->form->toHTML();
	 }
}



function newDispatcher(&$cp)
{
    $atd =& new CoopView(&$cp, $cp->vars['last']['table'], $none);

// cheap newDispatcher
//confessArray($cp->vars['last'],'req');
    switch($cp->vars['last']['action']){
//// EDIT AND NEW //////
    case 'new':
    case 'add':					//  for OLD menu system
    case 'edit':
        formaggio(&$cp);
        //confessArray($_DB_DATAOBJECT_FORMBUILDER, 'dbdofb');
        break;

//// DETAILS //////
    case 'details':

        $atd->fullText = true;    // force details to show all
        // MUST DO THIS! FIRST! please find a better way, this sucks
        $atd->obj->{$atd->pk} = $cp->vars['last']['id'];

        // object-specific override if needed
        if(is_callable(array($atd->obj, 'fb_display_details'))){
            print $atd->obj->fb_display_details(&$atd);
            break;
        }
     

        $id = $cp->vars['last']['id'];
        $atd->obj->{$atd->pk} = $id;
        $atd->obj->find(true);		//  XXX aack! need this for summary
        print $atd->horizTable();
     
        // try to intelligently find all forward/backlinks
        // or intermediately, adapt findfamily, and pass a list of tables
        // let the code go fish out the path to 'em

        foreach($atd->allLinks() as $table => $ids){
            list($nearid, $farid) = $ids;
            $cp->printDebug("$atd->table  link for $nearid {$atd->obj->$nearid}  $table", 4);
            $aud =& new CoopView(&$cp, $table, &$atd);
            $tabs = $aud->obj->table();
            $farwhole = $farid;
            if(!empty($tabs[$farid])){
                $farwhole = "{$aud->table}.$farid";
            }
            $aud->obj->whereAdd(sprintf('%s = %d', 
                                        $farwhole, $atd->obj->{$nearid}));
            //confessObj($aud, 'aud');
            $aud->debugWrap(5);
            print $aud->simpleTable();
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
                print $real->simpleTable();
            }
        }

        // standard audit trail, for all details
        $aud =& new CoopView(&$cp, 'audit_trail', &$atd);
        $aud->obj->table_name = $atd->table;
        $aud->obj->index_id = $id;
        $aud->obj->orderBy('updated desc');
        print $aud->simpleTable();


        if($cp->vars['last']['realm']){
            $realm =& new CoopView(&$cp, 'realms', &$atd);
            $realm->obj->get($cp->vars['last']['realm']);
            print $cp->selfURL(
                array('value' => "Click here for complete audit trail of all {$realm->obj->short_description}",
                      'inside' => array('table' => 'audit_trail',
                                        // XXX realm_id superflouos, using last!
                                        'realm_id' => $cp->vars['last']['realm'])));
        }

        print $cp->selfURL(
            array('value' => 'Click here for detailed view of Permissions',
                  'inside' => array('table' => $atd->table,
                                    'action' => 'perms')));
        break;
 
    case 'perms':
        print $atd->showPerms();
        break;


////CONFIRMDELETE
    case 'confirmdelete':

        if($res = bruteForceDeleteCheck(&$cp)){
            print $res;
            break;
        }

        print "<p>Are you sure you wish to delete this? Click 'Delete' or 'Cancel' to go back.</p>";	 
        $atdf = new CoopForm(&$cp, $cp->vars['last']['table'], $none); 
        $atdf->build(array_merge_recursive($_REQUEST, 
                                           $cp->vars['last']['submitvars']));

        $atdf->form->addElement('hidden', 'action', 'delete'); 
        $atdf->form->addElement('hidden', 'table', $cp->vars['last']['table']); 

        $atdf->legacyPassThru();

        $atdf->addRequiredFields();


        // change the save button and action to delete
        $el =& $atdf->form->getElement('savebutton');
        $el->setValue('Delete');

	 
        //TODO and add a cancel button
        //$atdf->form->addElement('button', 'cancelbutton', 'Cancel');

        $atdf->form->freeze();

        print $atdf->form->toHTML();

        break;




//// DELETE ////
    case 'delete':
        // hack , but it works. why reinvent the wheel?
        $atdf = new CoopForm(&$cp, $cp->vars['last']['table'], $none); 
        $atdf->build(array_merge_recursive($_REQUEST, 
                                           $cp->vars['last']['submitvars']));
;
        $atdf->obj->delete();
        print genericView(&$cp);

        break;





//// DEFAULT (VIEW) //////
    default:
        print genericView(&$cp);
        break;
    }

} // END NEWDISPATCHER




/////////////////////// MAIN ////////////



////////////////{{{STACK HANDLING. move to cooppage?
$atd =& new CoopView(&$cp, $_REQUEST['table'], $none);
$formatted = array('table'=>$_REQUEST['table'], 
                          'action' =>$_REQUEST['action'], 
                          'id' =>$_REQUEST[$atd->prependTable($atd->pk)],
                          'realm' => $_REQUEST['realm'] ? $_REQUEST['realm'] : 
                          $cp->vars['last']['realm']);

if(isset($_REQUEST['push'])){
    $cp->printDebug('PUSHING onto the stack!', 1);
    $cp->vars['stack'][] = $cp->vars['last'];
} 

// ALWAYS use formatted as last. 
$cp->vars['last'] =  $formatted;



if($sp= $cp->stackPath()){
    print "<p>YOUR NAVIGATION: $sp</p>";
}

//////////////}}} END STACK HANDLING


//in case of bug
if(!$cp->vars['last']['table']){
    print $cp->selfURL(array('value' =>'Unspecified table. Go back to home.', 
                             'inside' =>'nothing', 
                             'base' =>'index.php'));
    done();
}


printf("<h3>%s</h3>",$atd->obj->fb_formHeaderText);


print "\n<hr></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div id="centerCol">';


newDispatcher(&$cp);



$cp->done();

////KEEP EVERTHANG BELOW

?>


<!-- END GENERIC -->


