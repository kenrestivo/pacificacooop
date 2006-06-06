<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// modified by ken restivo <ken@restivo.org> 2005
// $Id$

require_once('HTML/QuickForm/Rule/Required.php');

/**
* Required elements validation
* @version     1.0
*/
class CustomRequired extends HTML_QuickForm_Rule_Required
{

    // OK. WHY is this necessary? becuase PEAR sucks.
    // pear quickform will only show a field as required if the
    // DEFAULT built-in pear quickform 'required' rule has been added.
    // but! that rule doesn't check for 0 values. mine does. well duh.
    // so, in order to use mine, ou have to add BOTH the default rule AND mine

    /**
     * Checks if an element is empty
     *
     * @param     string    $value      Value to check
     * @param     mixed     $options    Not used yet
     * @access    public
     * @return    boolean   true if value is not empty
     */
    function validate($value, $options = null)
    {
        //PEAR::raiseError('why isntthis getting callled??!', 111);
		if($value == '' || (is_numeric($value) && $value < 1)){
            //user_error("HEY [$value] is EMPTY", E_USER_NOTICE);
            return false;
        }
        //user_error("HEY [$value] is NOT EMPTY", E_USER_NOTICE);
        return true;
    } // end func validate


    function getValidationScript($options = null)
    {
        return array('', "{jsVar} == '' || {jsVar} < 1");
    } // end func getValidationScript

} // end class CustomRequired
?>
