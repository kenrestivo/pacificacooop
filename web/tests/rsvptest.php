<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopMenu.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');
require_once 'HTML/QuickForm/Controller.php';



$debug = 2;

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
     class FormBuilderPage extends HTML_QuickForm_Page {
        function buildForm() {
          $this->_formBuilt = true;
		  $tick =& new CoopForm(&$this->controller->page, 'tickets', &$nothing);
          $tick->obj->fb_createSubmit = false;
          $tick->useForm($this);
          $tick->build(); 
          $this->addElement('submit', $this->getButtonName('next'), 'Next >>');
        }
      }
      $cont =& new HTML_QuickForm_Controller('FBController');
	  $cont->page =& $cp;
      $cont->addPage(new FormBuilderPage('FBPage'));
      $cont->run();				// prints to screen


	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp);

	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END RSVPTEST -->


