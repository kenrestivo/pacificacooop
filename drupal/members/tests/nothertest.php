<?php

require_once('object-config.php');

require_once('DB/DataObject/FormBuilder.php');
require_once('objects/Users.php');


// calling it out by name... with no luck
$do =& new Users();
// Insert "$do->get($some_id);" here to edit an existing object instead
$fg =& DB_DataObject_FormBuilder::create($do);
$form =& $fg->getForm();
if ($form->validate()) {
	$form->process(array(&$fg,'processForm'), false);
	$form->freeze();
}
$form->display();

?>