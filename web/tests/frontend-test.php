<?php
//$Id$
define('DB_DATAOBJECT_NO_OVERELOAD', true);
require_once('DB/DataObject.php');
$config = parse_ini_file('../coop-dbobj.ini', true);
foreach($config as $class => $values) {
    $options = &PEAR::getStaticProperty($class, 'options');
    $options = $values;

}
// HACK for "tests" directory. remove this.
$options = &PEAR::getStaticProperty('DB_DataObject','options');
$options['schema_location'] = "../" . $options['schema_location'];
$options['class_location'] = "../" . $options['class_location'];
print_r_html($config);
$_DB_DATAOBJECT_FORMBUILDER['CONFIG'] = $config['DB_DataObject_FormBuilder'];

function print_r_html($val, $return = false) {
    $ret = '<pre>
'.htmlentities(print_r($val, true)).'
</pre>';
    if($return) {
        return $ret;
    }
    echo $ret;
}
include('DB/DataObject/FormBuilder/Frontend.php');

$frontend = new DB_DataObject_FormBuilder_Frontend();
$frontend->display();
?>