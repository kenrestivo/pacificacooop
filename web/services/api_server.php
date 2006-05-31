<?php


/// this one uses cooppage adn cookies

require_once('CoopPage.php');
require_once('COOP/NewDispatcher.php');
require_once('lib/jsonrpcserver.php');
require_once('PEAR.php');

$debug = 4;

class ExposeAPI {
    var $page;

    function ExposeAPI(&$page)
        {
            $this->page =& $page;
        }

    



} // end test class


$cp =& new CoopPage($debug);

$cp->printDebug('hey this is a jsonrpc request');

$cp->logIn(); /// same hack as in new template crap
$cp->getBrowserData(); // and why not

$server = new JSON_RPC_Server();

$server->registerClass(new ExposeAPI(&$cp));
$server->handleRequest();

$cp->finalDebug();

?>