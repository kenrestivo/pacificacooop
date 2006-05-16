<?php

chdir('..'); // for tests


require_once("first.inc");
require_once("auth.inc");

require_once("CoopPage.php");
require_once("CoopMenu.php");
require_once("CoopView.php");
require_once('HTML/TreeMenu.php');



/// let's try mine now
$cp =& new CoopPage($debug);
$cp->logIn(); // gotta do that for testing

$menu =& new CoopMenu(&$cp);
$menu->build();
print $menu->getDHTML();

print $menu->getListBox();


?> 