<?php
/**
 * Test class used in other examples
 * Constructors and private methods marked with _ are never exported in proxies to JavaScript
 * 
 * @category   HTML
 * @package    AJAX
 * @author     Joshua Eichorn <josh@bluga.net>
 * @copyright  2005 Joshua Eichorn
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/HTML_AJAX
 */

require_once('CoopPage.php');

class test {
    var $page; // cache of coop page

	function test(&$cp) {
        $this->page =& $cp;
	}
	function _private() {
	}
	function echo_string($string) {
        $this->page->printDebug("echo string $string", 2);
		return $string;
	}
	function slow_echo_string($string) {
		sleep(2);
		return $string;
	}
	function error_test($string) {
		trigger_error($string);
	}

	function userinfo($string) {
        //confessObj($this->page, 'page');
        $this->page->confessArray($_SESSION,
                                  "session", 2);
		//return $this->page->userStruct;
		return $this->page;
	}

}
?>
