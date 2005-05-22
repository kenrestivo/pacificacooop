<?php

//$Id$

chdir('../'); // XXX for test and/or maint
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');
require_once('lib/qfc_custom.php');
require_once('HTML/QuickForm/Controller.php');
require_once('HTML/QuickForm/Action.php');
require_once 'HTML/QuickForm/Action/Direct.php';


PEAR::setErrorHandling(PEAR_ERROR_PRINT);

//$debug = 3;


	 
class View extends HTML_QuickForm_Page
{
	function buildForm()
		{
			$this->_formBuilt = true;

			$this->addElement('submit',     $this->getButtonName('next'), 
							  'Add New');

			$res = "<p>lots of nothing</p>";
			
			$this->addElement('static', 'viewstuff', false, $res); 


			// XXX only for simple with no coopform! build does it.
			//confessObj($this->controller->cp, 'cp');
			if($sid = thruAuthCore($this->controller->cp->auth)){
				$this->addElement('hidden', 'coop', $sid); 
			}


			$this->setDefaultAction('next');
			//PEAR::raiseError('how did i get here?', 555);
		}
}

	 
class SimplePage extends HTML_QuickForm_Page
{
	function buildForm()
		{
			$this->_formBuilt = true;


			$this->addElement('submit',   $this->getButtonName('view'), 
				'View All');

			$this->addElement('header',     null, 
							  'Controller example 1: a simple form');
			$this->addElement('text',       'tstText', 
							  'Please enter something:', 
							  array('size'=>20, 'maxlength'=>50));


			$nav[] =& $this->createElement(
				'submit',   $this->getButtonName('next'), 'Next >>');
			$this->addGroup($nav, null, '', '&nbsp;', false);

				 
			$this->applyFilter('tstText', 'trim');
			$this->addRule('tstText', 'Pretty please!', 'required');

			// XXX only for simple with no coopform! build does it.
			//confessObj($this->controller->cp, 'cp');
			if($sid = thruAuthCore($this->controller->cp->auth)){
				$this->addElement('hidden', 'coop', $sid); 
			}

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

			$this->setDefaultAction('next');
		}
}



class ActionProcess extends HTML_QuickForm_Action
{
	function perform(&$page, $actionName)
		{
			print $page->controller->cp->flushBuffer();
			echo "Submit successful!<br>\n<pre>\n";
			confessObj($page->controller, 'yay');
			echo "\n</pre>\n";
				 
			//clean up after yourself, and bring me back to top!
			$page->controller->container(true);
			$view =& $page->controller->getPage('view');

			print $view->handle('display');
		}

}


///////// MAIN

$cp = new coopPage( $debug);
$cp->buffer($cp->pageTop());


$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
$cp->buffer($menu->topNavigation());

$cp->buffer("<p>QFC test</p>");


$controller =& new HTML_QuickForm_Controller('simpleForm');
$controller->cp =& $cp; // DO THIS FIRST!!


$controller->addPage(new View ('view'));
$controller->addPage(new SimplePage('page1'));
$controller->addPage(new SecondPage('page2'));


// This is the action we should always define ourselves
$controller->addAction('process', new ActionProcess());

// need this for fooey stuff
$controller->addAction('view', new HTML_QuickForm_Action_Direct());

// self-explanatory
$controller->addAction('display', new CustomDisplay());


$controller->run();


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END QFC TEST -->


