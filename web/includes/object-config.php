<?php

//$Id$

require_once('Config.php');		// do i really need this??

$config = parse_ini_file('coop-dbobj.ini',TRUE);
foreach($config as $class=>$values) {
    $options = &PEAR::getStaticProperty($class,'options');
    $options = $values;
}

$_DB_DATAOBJECT_FORMBUILDER['CONFIG'] = $config['Db_DataOject_FormBuilder'];

?>