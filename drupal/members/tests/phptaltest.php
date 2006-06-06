<?php

require_once "HTML/Template/PHPTAL.php";


class Template
{
    // assume this macro is coming out of a database,
    // for illustration purposes i hard-code it here
    var $testmacro = '<p tal:content="realpath">real page</p>
                     <h1 tal:content="title">sample title</h1>';
}



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


// create a new template object
$template = new PHPTAL("template-phptal-test.html");

$template->set('realpath', $template->realPath());

// let's create an array of objects for test purpose
$result = array();
$result[] = new Person("foo", "01-344-121-021");
$result[] = new Person("bar", "05-999-165-541");
$result[] = new Person("baz", "01-389-321-024");
$result[] = new Person("buz", "05-321-378-654");

// put some data into the template context
$template->set("title", "the title value");
$template->set("result", $result);

$tmpl = new Template();
$template->set("templ", $tmpl);



// execute template
$res = $template->execute();
// result may be an error
if (PEAR::isError($res)) {
    echo $res->toString(), "\n";
} else {
    echo $res;
}

?>
