<?php

//$Id$


$config = parse_ini_file('coop-dbobj.ini',TRUE);
foreach($config as $class=>$values) {
    $options = &PEAR::getStaticProperty($class,'options');
    $options = $values;
}

// to hack around dbobject trashing my settings
$_DB_DATAOBJECT_FORMBUILDER['CONFIG'] = $config['DB_DataObject_FormBuilder'];

?>