<?php 

//$Id$

require_once('DB/DataObject.php');
require_once('DB/DataObject/FormBuilder.php');

require_once('object-config.php');

$obj =& DB_DataObject::factory ('families_income_join'); // & instead?
if (PEAR::isError($obj)){
	die ($obj->getMessage ());
}

//print_r($this);
$obj->get(10);
$build =& DB_DataObject_FormBuilder::create ($obj);
$form =& $build->getForm();
$form->freeze();
if($form->validate ()){
	$res = $form->process (array 
						   (&$build, 'processForm'), 
						   false);
	if ($res){
		$obj->debug('processed successfully', 
						  'detailform', 0);
		header('Location: %s', $_SERVER['PHP_SELF']);
	}
}

$form->display();
	
print"<pre>";
print "======== BUILDER ============\n";
print htmlentities(print_r($build, 1));
print "======== FORM ============\n";
print htmlentities(print_r($form, 1));
print "</pre>";


////KEEP EVERTHANG BELOW

?>
<!-- END TRASH -->


