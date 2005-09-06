<?php

//$Id$

$debug = 4;


require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');


PEAR::setErrorHandling(PEAR_ERROR_PRINT);


$cp = new coopPage( $debug);
print $cp->pageTop();


$menu =& new CoopMenu(&$cp);
print $menu->topNavigation();


//in case of bug
if(!$_REQUEST['table']){
    print $cp->selfURL(array('value' =>'Unspecified table. Go back to home.', 
                             'inside' =>'nothing', 
                             'base' =>'index.php'));
    done();
}


$atd =& new CoopView(&$cp, $_REQUEST['table'], $none);

////////////////{{{STACK HANDLING. move to cooppage?
$formatted = array('table'=>$_REQUEST['table'], 
                          'action' =>$_REQUEST['action'], 
                          'id' =>$_REQUEST[$atd->prependTable($atd->pk)],
                          'realm' => $_REQUEST['realm'] ? $_REQUEST['realm'] : 
                          $cp->vars['last']['realm']);

if(isset($_REQUEST['push'])){
    $cp->vars['stack'][] = $formatted;
}
$cp->vars['last'] = $formatted;

if($sp= $cp->stackPath()){
    print "<p>YOUR NAVIGATION: $sp</p>";
}


//////////////}}} END STACK HANDLING

printf("<h3>%s</h3>",$atd->obj->fb_formHeaderText);


print "\n<hr></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div id="centerCol">';


function genericView(&$atd)
{

    $atd =& new CoopView(&$atd->page, $_REQUEST['table'], $none);
    //$atd->obj->debugLevel(2);



     if(is_callable(array($atd->obj, 'fb_display_view'))){
         $atd->page->printDebug('calling callback for view', 2);
         return $atd->obj->fb_display_view();
     }


     //$atd->debugWrap(2);

    //TODO: some variation on the old "perms display" from auth.inc
    //maybe at or top of doc? with editor to change them? ;-)
    return $atd->simpleTable();
			
}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
//// EDIT AND NEW //////
 case 'new':
 case 'add':					//  for OLD menu system
 case 'edit':
	 // NOT the coopView above!
	 $atdf = new CoopForm(&$cp, $_REQUEST['table'], $none); 


	 $atdf->build($_REQUEST);


	 // ugly assthrus for my cheap dispatcher
	 $atdf->form->addElement('hidden', 'action', $_REQUEST['action']); 
	 $atdf->form->addElement('hidden', 'table', $_REQUEST['table']); 

	 $atdf->legacyPassThru();

	 $atdf->addRequiredFields();
	 
	 // make-um bigger


	 if ($atdf->validate()) {
		 print "saving...";
		 print $atdf->form->process(array(&$atdf, 'process'));
         // only go back to view if previous state was 'edit'
         if($_REQUEST['action'] == 'edit'){
             print genericView(&$atd);
         }else {
             confessArray($_REQUEST, 'request before redisplay');
             print $atdf->form->toHTML();
         }
	 } else {
		 print $atdf->form->toHTML();
	 }

	 //confessArray($_DB_DATAOBJECT_FORMBUILDER, 'dbdofb');
	 break;

//// DETAILS //////
 case 'details':

     $atd->fullText = true;    // force details to show all
     // MUST DO THIS! FIRST! please find a better way, this sucks
     $atd->obj->{$atd->pk} = $_REQUEST[$atd->prependTable($atd->pk)]; 

     if(is_callable(array($atd->obj, 'fb_display_details'))){
         print $atd->obj->fb_display_details();
         break;
     }
     
     // TODO: in future, try to intelligently find all forward/backlinks
     // or intermediately, adapt findfamily, and pass a list of tables
     // let the code go fish out the path to 'em

	 $id = $_REQUEST[$atd->prependTable($atd->pk)];
     $atd->obj->{$atd->pk} = $id;
     $atd->obj->find(true);		//  XXX aack! need this for summary
     print $atd->horizTable();
     

     // NIFTY CODE TO SHOW ALL LINKS!
     foreach(array_merge($atd->backlinks, $atd->forwardLinks) as $key =>$val){
         
         // backlinks and forward links are different, alas
         if(strstr($val, ':')){
             $nearid = $key;
             list($table, $farid) = explode(':', $val);
         } else {
             $table = $key;
             $nearid = $farid = $val;
         }

         if($table == $atd->table){ // blow off recursion
             continue;
         }


         // for now, blow off the jointables
         $cp->printDebug("$atd->table  link for $nearid {$atd->obj->$nearid}  $table<br>", 4);
         $aud =& new CoopView(&$cp, $table, &$atd);
         $aud->obj->{$farid} = $atd->obj->{$nearid};
         //confessObj($aud, 'aud');
         $aud->debugWrap(4);
         print $aud->simpleTable();
     }


     // standard audit trail, for all details
     $aud =& new CoopView(&$cp, 'audit_trail', &$atd);
     $aud->obj->table_name = $atd->table;
     $aud->obj->index_id = $id;
     $aud->obj->orderBy('updated desc');
     print $aud->simpleTable();


     print $cp->selfURL(
         array('value' => 'Click here for detailed view of Permissions',

               'inside' => array('table' => $atd->table,
                                 'action' => 'perms')
               ));
 	 break;
 
 case 'perms':
     print $atd->showPerms();
     break;


////CONFIRMDELETE
 case 'confirmdelete':
	 print "<p>Are you sure you wish to delete this? Click 'Delete' below to delete it, or the 'Back' button in your broswer to cancel.</p>";	 
     $atdf = new CoopForm(&$cp, $_REQUEST['table'], $none); 
	 $atdf->build($_REQUEST);

	 $atdf->form->addElement('hidden', 'action', 'delete'); 
	 $atdf->form->addElement('hidden', 'table', $_REQUEST['table']); 

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
	 $atdf = new CoopForm(&$cp, $_REQUEST['table'], $none); 
	 $atdf->build($_REQUEST);
	 $atdf->obj->delete();
	 print genericView(&$atd);

	 break;





//// DEFAULT (VIEW) //////
 default:
	 print genericView(&$atd);
	 break;
}



$cp->done ();

////KEEP EVERTHANG BELOW

?>


<!-- END GENERIC -->


