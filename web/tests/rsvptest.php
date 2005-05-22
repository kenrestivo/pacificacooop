<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopMenu.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');
require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action.php';



//$debug = 0;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();



$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>RSVP Test</p>";

print $cp->selfURL('View Tickets');
print $cp->selfURL('Add New Ticket', array('action' => 'new'));


function viewHack(&$cp)
{
	// nipped from invitations_cash.inc

	//$lij->recordActions = array('edit' => "Edit",
	//						'details' => "Details");
	$inc =& new CoopObject(&$cp, 'income', &$nothing);
	$inc->obj->school_year = $sy;
	$lij =& new CoopView(&$cp, 'leads_income_join', &$nothing);
	$inv && $lij->obj->joinAdd($copy2);
	$lij->obj->joinAdd($inc->obj);
	$res .= $lij->simpleTable();
	
	
	//$tick->recordActions = array('edit' => "Edit",
	//						'details' => "Details");
	$tick =& new CoopView(&$cp, 'tickets', &$nothing);
	$tick->obj->school_year = $sy;
	$tick->obj->whereAdd('tickets.lead_id is not null');
	$tick->obj->fb_fieldsToRender = array('income_id', 'lead_id', 'ticket_quantity', 
										  'ticket_type', 'vip_flag');
	$inv && $tick->obj->joinAdd($copy2);
	$res .= $tick->simpleTable();
	
	return $res; 
}

// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
	 
//// EDIT AND NEW //////
 case 'new':
 case 'edit':
	 
	 class SimplePage extends HTML_QuickForm_Page
	 {
		 function buildForm()
			 {
				 $this->_formBuilt = true;
				 
				 $atdf = new CoopForm(&$this->controller->cp, 'blog_entry', 
									  $none); // NOT the coopView above!

				 $atdf->obj->fb_addNewLinkFields = array();	// don't let 'em add new fams

				 $atdf->obj->fb_fieldsToRender = array( 'short_title', 'body', 
														'show_on_members_page', 
														'show_on_public_page');
 
	
				 $atdf->obj->fb_createSubmit = false; // important!

				 $atdf->useForm(&$this);

				 $atdf->build($_REQUEST);
				 

				 // XXX gah, hack around the hokey
				 $this->CoopForm =& $atdf;

				 //confessObj($this->controller, 'thiscontroller');

				 // ugly assthrus for my cheap dispatcher
				 $atdf->form->addElement('hidden', 'action', 'edit'); 

				 $atdf->legacyPassThru();

				 $atdf->addRequiredFields();

				 $atdf->setDefaults();

				 // still a sub-element of my cheap dispatcher!
				 $this->addElement('hidden', 'action', 'edit'); 

				 $this->setDefaultAction('submit');

				 // Bind the button to the 'submit' action
				 $this->addElement('submit',     
								   $this->getButtonName('submit'), 'Send');

			 }
	 }


	 class ActionProcess extends HTML_QuickForm_Action
	 {
		 function perform(&$page, $actionName)
			 {
				 //confessObj($this, 'action');
				 //XXX this only sends the current page's vars through, no?
				 print $page->process(array(&$page->CoopForm, 'process'));

				 ///XXX do i do this here??!
				 ///if there's one process for each form?
				 $page->controller->container(true);
			 }
	 }

	 $page =& new SimplePage('page1');


	 // This is the action we should always define ourselves
	 $page->addAction('process', new ActionProcess());

	 $controller =& new HTML_QuickForm_Controller('simpleForm');
	 $controller->cp =& $cp; // DO THIS FIRST!!
	 $controller->addPage($page);
	 $controller->run();



	 break;

//// DEFAULT (VIEW) //////
 default:
	 //print viewHack(&$cp);
	 print "nothing";
	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END RSVPTEST -->


