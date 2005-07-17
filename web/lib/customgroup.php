<?php

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
										$selectboxelement)
		{
			//user_error("customgroup constructor called", E_USER_NOTICE);
			$elements[] =& $selectboxelement;
			
			return HTML_QuickForm_group::HTML_QuickForm_group(
				$elementName, $elementLabel, $elements, "<br>");
			
		}

/*

				$select->_parentForm =& $this->form;

				// MAKE SUBFORM
				$subformname = sprintf('%s-%s-subform', $this->table, $key);
				$sub =& $this->addSubTable($key);
				$subform =& HTML_QuickForm::createElement(
					'customsubform', 
					$subformname,
					array('id' => $subformname, 
						  'class' => 'hidden'), // XXX hidden here???
					$sub->form);

				// THE HIDDEN
				$hiddenname = sprintf('%s-subtables-%s',
									  $this->table, $key);
				$hidden =& HTML_QuickForm::createElement(
					'hidden', $hiddenname,
					$vars[$hiddenname] ? $vars[$hiddenname] : 0,
					array('id' => $hiddenname)); // getelementbyid
				

				// MAKE GROUP
				$group =& $this->form->addElement(
					'customgroup', $fullkey . "-group", false,
					array($select, $subform, $hidden), '<br/>', false,
					"fubar");
				

				// THE RULES
				if($this->obj->fb_requiredFields[$key]){
					// yank from requiredfields at top level
					unset($this->obj->fb_requiredFields[$key]);

					$this->form->addGroupRule(
						$group->getName(),
						array($fullkey => array(
								  "$key mustn't be empty", 'customrequired'
								  )));

					$this->form->addRule($group->getName(),
										 "$key mustn't be empty",
										 'customrequired'
										 );
					$this->form->_required[] = $group->getName();

					//TODO: deal with required fields rules!!
					//TODO: add group rules
				}


*/


} // end class Customgroup

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('customgroup',
										'lib/customgroup.php', 
										'HTML_QuickForm_customgroup');
}


?>
