<?php

	#  Copyright (C) 2004-2005  ken restivo <ken@restivo.org>
	# 
	#  This program is free software; you can redistribute it and/or modify
	#  it under the terms of the GNU General Public License as published by
	#  the Free Software Foundation; either version 2 of the License, or
	#  (at your option) any later version.
	# 
	#  This program is distributed in the hope that it will be useful,
	#  but WITHOUT ANY WARRANTY; without even the implied warranty of
	#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	#  GNU General Public License for more details. 
	# 
	#  You should have received a copy of the GNU General Public License
	#  along with this program; if not, write to the Free Software
	#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

//$Id$

require_once "HTML/Template/PHPTAL.php";


// create a new template object
$template = new PHPTAL("tests/template-phptal-test.html");

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

$template->set('realpath', $template->realPath());
// execute template
$res = $template->execute();
// result may be an error
if (PEAR::isError($res)) {
    echo $res->toString(), "\n";
} else {
    echo $res;
}


?>