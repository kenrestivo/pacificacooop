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



$debug = 0;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();



$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>RSVP Test</p>";

print $cp->selfURL('View Tickets');
print $cp->selfURL('Add New Ticket', array('action' => 'new'));
print $cp->selfURL('FUBAR', array('action' => 'fubar'));


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
	 
 // i dislike instantiating classes inside conditional code, but, oh well.

	 class ActionProcess extends HTML_QuickForm_Action
	 {
		 function perform(&$page, $actionName)
			 {
				 echo '<h1>Hello, ' . htmlspecialchars($page->exportValue('name')) . '!</h1>';
				 PEAR::raiseError("i'm here and i'm processing, dammit", 111);
			 }
	 }


     class FormBuilderPage extends HTML_QuickForm_Page {
        function buildForm() {
          $this->_formBuilt = true;
		  $tick =& new CoopForm(&$this->controller->cp, 'tickets', &$nothing);
          $tick->obj->fb_createSubmit = false;
          $tick->useForm($this);
          $tick->build(); 
		  // ugly assthrus for my cheap dispatcher
		  $tick->form->addElement('hidden', 'action', 'fubar'); // XXX!!!
		  
		  $tick->legacyPassThru();
		  
		  $tick->addRequiredFields();
	 
          $this->addElement('submit', $this->getButtonName('next'), 'Next >>');
          //$this->addElement('submit', $this->getButtonName('send'), 'Save');
        }
      }


      $cont =& new HTML_QuickForm_Controller('FBController');
	  $cont->cp =& $cp;

	  $testpage =  new FormBuilderPage('FBPage');
	  $testpage->addAction('process', new ActionProcess());

      $cont->addPage(&$testpage);

      $cont->run();				// prints to screen


	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp);
	 //print "nothing";
	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END RSVPTEST -->


