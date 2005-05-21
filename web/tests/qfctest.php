<?php

//$Id$

chdir('../'); // XXX for test and/or maint
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');
require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action.php';
require_once 'HTML/QuickForm/Action/Jump.php';


PEAR::setErrorHandling(PEAR_ERROR_PRINT);

//$debug = 3;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();


$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>QFC test</p>";


print $cp->selfURL('View');
print $cp->selfURL('Create New', array('action' => 'new'));
				   
$atd = new CoopView(&$cp, 'job_descriptions', $none);
$atd->recordActions = array('edit' => "Edit",
							'details' => 'Details'); 

function viewHack(&$cp, &$atd)
{
	print 'nothing';
	// return $atd->simpleTable();
			
}


class JumpSucks extends HTML_QuickForm_Action_Jump
{
	function perform(&$page, $actionName)
		{
			// ok, this is a cut-and-paste from action_jump,
			// but does display not location

			//print "jumpy jump [$actionName]";
			//confessObj($page, 'the page receiving the jump');

			// check whether the page is valid before trying to go to it
			if ($page->controller->isModal()) {
				// we check whether *all* pages up to current are valid
				// if there is an invalid page we go to it, instead of the
				// requested one
				$pageName = $page->getAttribute('id');
				if (!$page->controller->isValid($pageName)) {
					$pageName = $page->controller->findInvalid();
				}
				$current =& $page->controller->getPage($pageName);

			} else {
				$current =& $page;
			}

			// ok, now call display on  $current!
			$current->handle('display');
		}

}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
//// EDIT AND NEW //////
 case 'new':
 case 'edit':
	 
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
				 
				 $this->addElement('submit',     $this->getButtonName('next'), 
								   'Send');
				 
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
	 $controller->addAction('jump', new JumpSucks());

	 $controller->run();

	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp, &$atd);
	 break;
}



done ();

////KEEP EVERTHANG BELOW

?>


<!-- END JOBDESCRIPTIONS -->


