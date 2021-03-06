<?php
/**
 * Advanced ussage of HTML_AJAX_Server
 * Allows for a single server to manage exporting a large number of classes without high overhead per call
 * Also gives a single place to handle setup tasks especially useful if session setup is required
 *
 * The server responds to ajax calls and also serves the js client libraries, so they can be used directly from the PEAR data dir
 * 304 not modified headers are used when server client libraries so they will be cached on the browser reducing overhead
 *
 * @category   HTML
 * @package    AJAX
 * @author     Joshua Eichorn <josh@bluga.net>
 * @copyright  2005 Joshua Eichorn
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/HTML_AJAX
 */

chdir('../'); // for test folder

include('CoopPage.php');

 // include the server class
include 'HTML/AJAX/Server.php';


$cp = new CoopPage($debug);
$cp->pageTop(); /// HAVE TO DO THIS TO FISH OUT THE AUTH STUFF FROM SESSION!

// extend HTML_AJAX_Server creating our own custom one with init{ClassName} methods for each class it supports calls on
class TestServer extends HTML_AJAX_Server {
    var $page; // cache of coop page

	// this flag must be set to on init methods
	var $initMethods = true;

    function TestServer(&$cp)
        {
            $this->page =& $cp;
            HTML_AJAX_Server::HTML_AJAX_Server();
        }


	// init method for the test class, includes needed files an registers it for ajax
	function initTest() {
		include 'test.class.php';
		$this->registerClass(new test(&$this->page));
    }

// 	function initCoopPage() {
//         $this->registerClass(&$cp);
//     }

	function initAnotherTest() {
		$this->registerClass(new AnotherTest());
    }

	
}

// create an instance of our test server
$server = new TestServer(&$cp);

// handle requests as needed
$server->handleRequest();

print $cp->done();

?>
