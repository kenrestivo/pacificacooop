<?php
require_once "HTML/Template/PHPTAL.php";

// create a new template object
$template = new PHPTAL("template-phptal-test.html");

// the Person class
class Person
{
    var $name;
    var $phone;
    function Person($name, $phone)
    {
        $this->name = $name;
        $this->phone = $phone;
    }
};

// let's create an array of objects for test purpose
$result = array();
$result[] = new Person("foo", "01-344-121-021");
$result[] = new Person("bar", "05-999-165-541");
$result[] = new Person("baz", "01-389-321-024");
$result[] = new Person("buz", "05-321-378-654");

// put some data into the template context
$template->set("title", "the title value");
$template->set("result", $result);

// execute template
$res = $template->execute();
// result may be an error
if (PEAR::isError($res)) {
    echo $res->toString(), "\n";
} else {
    echo $res;
}

?>
