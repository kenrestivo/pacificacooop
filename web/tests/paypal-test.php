<?php 

require_once("shared.inc");
require_once("HTML/QuickForm.php");
require_once("HTML/Table.php");
require_once("DB.php");

print "<HTML>
		<HEAD>
			<TITLE>TESTING</TITLE>
		</HEAD>

		<BODY>

	";

// TODO: use array instead
$dbh =& DB::connect("mysql://input:test@bc/coop");
if (DB::isError($dbh)) {
    die($dbh->getMessage());
}
$dbh->setFetchMode(DB_FETCHMODE_ASSOC);

$colnames = array ('privilege_id', 'user_id', 'group_id', 'realm', 
				   'user_level', 'group_level');

function
formOne()
{
	$form = new HTML_QuickForm('test', 'get');
	$form->addElement('header', 'testheader', 'this is a test');
	$form->addElement('text', 'testtext', 'What is your name?');
	$form->addElement('header', 'testheader1', 'can i have another?');

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

	$form->addRule('testtext', 'Size', 
				   'required', '', 'client');

	// hmm. i can do cool stuff here with conditionals!
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
 
function
testQuery($dbh, $colnames)
{
	$res =& $dbh->query(sprintf(
							"select %s from user_privileges", 
							implode(", ", $colnames)));
	if (DB::isError($dbh)) {
		die($dbh->getMessage());
	}

	$tab =& new HTML_Table();
	$tab->addRow( $colnames);
	$tab->setRowType(0, 'th');
	while ($row = $res->fetchrow()){
		$tab->addRow(array_values($row));
	}

	$tab->display();
									
}


formOne();
formTwo();
testQuery($dbh, $colnames);



done();

$dbh->disconnect();

?>

 