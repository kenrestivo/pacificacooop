<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// modified by ken restivo <ken@restivo.org> 2005
// $Id$

require_once('HTML/QuickForm/group.php');

/**
* Required elements validation
* @version     1.0
*/
class HTML_QuickForm_customgroup extends HTML_QuickForm_group
{

    function HTML_QuickForm_customgroup($elementName=null, $elementLabel=null, 
										$elements=null, $separator=null, 
										$appendName = true)
		{
			//user_error("customgroup constructor called", E_USER_NOTICE);
			return HTML_QuickForm_group::HTML_QuickForm_group(
				$elementName, $elementLabel, 
				$elements, $separator, 
				$appendName);
			
		}

} // end class Customgroup

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('customgroup',
										'lib/customgroup.php', 
										'HTML_QuickForm_customgroup');
}


?>
