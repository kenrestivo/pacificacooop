<?php


/// this one uses cooppage adn cookies

require_once('CoopPage.php');
require_once('CoopNewDispatcher.php');
require_once('lib/jsonrpcserver.php');
require_once('PEAR.php');

$debug = 4;

class ExposeAPI {
    var $page;

    function ExposeAPI(&$page)
        {
            $this->page =& $page;
        }


    function getPage()
        {
            return $this->page;
        }


    function execute($tablename, $method, $args = null)
        {
            $co =& new CoopObject(&$this->page, $tablename, &$nothing);
            return call_user_func_array(array($co, $method), $args);
        }


    function getMethods($tablename, $do = false)
        {
            $co =& new CoopObject(&$this->page, $tablename, &$nothing);
            return $do ? get_class_methods($co->obj) : get_class_methods($co) ;
        }



    function getVars($tablename, $do = false)
        {
            $co =& new CoopObject(&$this->page, $tablename, &$nothing);
            return $do? get_object_vars($co->obj) : get_object_vars($co) ;
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