<?php

//$Id$

chdir('../'); // XXX for test and/or maint
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');
require_once('lib/qfc_custom.php');
require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action.php';


PEAR::setErrorHandling(PEAR_ERROR_PRINT);

//$debug = 3;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$hack .= $cp->pageTop();


$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
$hack .= $menu->topNavigation();

$hack .= "<p>QFC test</p>";


$hack .= $cp->selfURL('View');
$hack .=  $cp->selfURL('Create New', array('action' => 'new'));
				   
$atd = new CoopView(&$cp, 'job_descriptions', $none);
$atd->recordActions = array('edit' => "Edit",
							'details' => 'Details'); 

function viewHack(&$cp, &$atd)
{
	print 'nothing';
	// return $atd->simpleTable();
			
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

				 $this->addElement('header',     null, 
								   'Controller example 1: a simple form');
				 $this->addElement('text',       'tstText', 
								   'Please enter something:', 
								   array('size'=>20, 'maxlength'=>50));
				 // Bind the button to the 'submit' action
				 $this->addElement('submit',     $this->getButtonName('next'), 
								   'Next>>');
				 
				 $this->applyFilter('tstText', 'trim');
				 $this->addRule('tstText', 'Pretty please!', 'required');

				 // XXX only for simple with no coopform! build does it.
				 //confessObj($this->controller->cp, 'cp');
 				 if($sid = thruAuthCore($this->controller->cp->auth)){
 					 $this->addElement('hidden', 'coop', $sid); 
 				 }

				 // still a sub-element of my cheap dispatcher!
				 $this->addElement('hidden', 'action', 'edit'); 

				 $this->setDefaultAction('next');
				 //PEAR::raiseError('how did i get here?', 555);
			 }
	 }

	 class SecondPage extends HTML_QuickForm_Page
	 {
		 function buildForm()
			 {
				 $this->_formBuilt = true;
				 
				 $this->addElement('header',     null, 
								   'Controller example 2: a simple form');
				 $this->addElement('text',       'moreText', 
								   'Please enter MORE:', 
								   array('size'=>20, 'maxlength'=>50));
				 

				 $nav[] =& $this->createElement(
					 'submit',   $this->getButtonName('back'), '<< Back');
				 $nav[] =& $this->createElement(
					 'submit',   $this->getButtonName('next'), 'Finish');
				 $this->addGroup($nav, null, '', '&nbsp;', false);
				 
				 $this->applyFilter('moreText', 'trim');
				 $this->addRule('moreText', 'Pretty please!', 'required');

				 // XXX only for simple with no coopform! build() does it.
				 //confessObj($this->controller->cp, 'cp');
 				 if($sid = thruAuthCore($this->controller->cp->auth)){
 					 $this->addElement('hidden', 'coop', $sid); 
 				 }

				 // still a sub-element of my cheap dispatcher!
				 $this->addElement('hidden', 'action', 'edit'); 

				 $this->setDefaultAction('next');
			 }
	 }



	 class ActionProcess extends HTML_QuickForm_Action
	 {
		 function perform(&$page, $actionName)
			 {
				 echo "Submit successful!<br>\n<pre>\n";
				 confessObj($page->controller, 'yay');
				 echo "\n</pre>\n";
			 }
	 }


	 $controller =& new HTML_QuickForm_Controller('simpleForm');
	 $controller->cp =& $cp; // DO THIS FIRST!!


	 $controller->addPage(new SimplePage('page1'));
	 $controller->addPage(new SecondPage('page2'));


	 // This is the action we should always define ourselves
	 $controller->addAction('process', new ActionProcess());

	 // self-explanatory
	 $controller->addAction('display', new CustomDisplay());
	 //$controller->addAction('jump', new JumpDisplay());

	 $controller->run();

	 break;

//// DEFAULT (VIEW) //////
 default:
	 print $hack;
	 print viewHack(&$cp, &$atd);
	 break;
}


//$cp->confessArray($_SESSION, 'session at end of page', 3);
done ();

////KEEP EVERTHANG BELOW

?>


<!-- END JOBDESCRIPTIONS -->


