<?php
//$Id$
define('DB_DATAOBJECT_NO_OVERLOAD', true);
require_once('DB/DataObject.php');
require_once('DB/DataObject/FormBuilder/Frontend.php');

chdir("../");                   // XXX only for "test" dir hack!
require_once("object-config.php");


$frontend = new DB_DataObject_FormBuilder_Frontend();
$frontend->display();

?>