<?php


/// this one uses cooppage adn cookies

require_once('CoopPage.php');
require_once('CoopNewDispatcher.php');
require_once('lib/jsonrpcserver.php');
require_once('PEAR.php');

$debug = 4;

class TestJunk {
    var $page;

    function TestJunk(&$page)
        {
            $this->page =& $page;
        }

    function echotest($text)
        {
            return $text;
        }
    

    function throwError()
        {
            user_error('intentionally broken method to test error handling', 
                       E_USER_ERROR);
        }


    function throwPEARError()
        {
            PEAR::raiseError('hah. pear error', 666);
        }

    function getPage()
        {
            return $this->page;
        }


    function dispatchTable($last)
        {
            $this->page->vars['last'] = get_object_vars($last);
            $disp =& new CoopNewDispatcher(&$this->page);
            return $disp->dispatch();
        }


} // end test class


$cp =& new CoopPage($debug);

$cp->printDebug('hey this is a jsonrpc request');

$cp->logIn(); /// same hack as in new template crap
$cp->getBrowserData(); // and why not

$server = new JSON_RPC_Server();

$server->registerClass(new TestJunk(&$cp));
$server->handleRequest();

$cp->finalDebug();

?>