<?php 

require_once("shared.inc");
require_once("HTML/QuickForm.php");
require_once("DB.php");

print "<HTML>
		<HEAD>
			<TITLE>TESTING</TITLE>
		</HEAD>

		<BODY>

	";


$dbh =& DB::connect("mysql://input:test@bc/coop");
if (DB::isError($dbh)) {
    die($dbh->getMessage());
}

function
formOne()
{
	$form = new HTML_QuickForm('test', 'get');
	$form->addElement('header', 'testheader', 'this is a test');
	$form->addElement('text', 'testtext', 'What is your name?');
	$form->addElement('header', 'testheader1', 'cani have another?');

	$form->addElement('reset', 'clearbutton', 'Clear');
	$form->addElement('submit', 'submitbutton', 'Submit');

	$form->addRule('testtext', ' name is required', 'required', '', 'client');


	if ($form->validate()) {
		// If the form validates then freeze the data
		$form->freeze();
	}


	$form->display();
}

function
formTwo()
{


	$defaults = array ( test2text => 'horsehockey',
						test1text => 'sheepshit');

	$form = new HTML_QuickForm('test2', 'put');
	$form->addElement('header', 'test2header', 'this is also a test');
	$form->addElement('text', 'test1text', 'What is your slock?');
	$form->addElement('text', 'test2text', 'What is your penis size?');
	$sumbits[] = 
		&HTML_QuickForm::createElement('reset', 'clear2button', 'Clear');
	$sumbits[] = 
		&HTML_QuickForm::createElement('submit', 'submit2button', 'Submit');
	$form->addGroup($sumbits, 'sumbit buttons', 'you sumbit!', '&nbsp;');

	$form->applyFilter(__ALL__, 'trim');

	$form->addRule('testtext', 'Penis size required', 
				   'required', '', 'client');

	if ($form->validate()) {
		// If the form validates then freeze the data
		$form->freeze();
		
		confessArray($_POST, "postvars");
		confessArray($GLOBALS, "globals");
		

	} else {
		$form->setDefaults($defaults);
		$form->display();	
	}
}

formOne();
formTwo();

done();

$dbh->disconnect();

?>

 