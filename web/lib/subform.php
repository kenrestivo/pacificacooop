<?php

// $Id$
// Copyright 2005 Justin Patrin


require_once('HTML/QuickForm/static.php');

if ( substr(phpversion(),0,1) != 5) {
    if (!function_exists('clone')) {
        // emulate clone  - as per php_compact, slow but really the correct behaviour..
        eval('function clone($t) { $r = $t; if (method_exists($r,"__clone")) { $r->
__clone(); } return $r; }');
    }
}

class HTML_QuickForm_SubForm extends HTML_QuickForm_static {
    var $_subForm;
    var $_parentForm;
    var $_name;
    var $_preValidationCallback;

    function HTML_QuickForm_SubForm($name=null, $label=null, $form=null)
    {
        if ($form !== null) {
            $this->setForm($form);
        }

// 		user_error("subform: [$name] [$label] [$options] [$attributes] [$form]", 
// 				   E_USER_NOTICE);


		//confessObj($this, 'subform');
		//return it?? use parent::?
        HTML_QuickForm_static::HTML_QuickForm_static($name, $label);
    }

    function setForm(&$form)
    {
        $this->_subForm =& $form;
    }


    function accept(&$renderer, $required = null, $error = null)
    {
        $this->_renderer = clone($renderer);
        $renderer->renderElement($this, $required, $error);
    }

    /**
     * renders the element
     *
     * @return string the HTML for the element
     */
    function toHtml()
    {
		//$this->_parentForm->CoopForm->page->printDebug("subform::tohtml() yes i am here", 666);

		// ugly way to reset al this, since it's cloned
        if (isset($this->_renderer)) {
            $this->_renderer->_html =
                $this->_renderer->_hiddenHtml =
                $this->_renderer->_groupTemplate =
                $this->_renderer->_groupWrap = '';
            $this->_renderer->_groupElements = array();
            $this->_renderer->_inGroup = false;
        } else {
            $this->_renderer = clone(HTML_QuickFor::default_Renderer());
        }
		// clever. do this in the template instead of on the final result
        $this->_renderer->setFormTemplate(
			preg_replace('!</?form[^>]*>!', '',
						 $this->_renderer->_formTemplate));
        $this->_subForm->accept($this->_renderer);

		list($table,  $field, $crap) = explode('-', $this->getName());
        return sprintf('<div class="%s" id="%s">
				<a href="javascript:void();" id="%s-%s-toggle"
	   onClick="toggleSubform(\'%s\',\'%s\')">&lt;&lt; Select Existing %s</a>%s</div>', 
					   'hidden', // TODO: check passthru!
					   $this->getName(),
					   $field,
					   $table,
					   $field,
					   $table,
					   '', 		// TODO: group label
					   $this->_renderer->toHtml());
    }

    function freeze()
    {
        parent::freeze();
        $this->_subForm->freeze();
    }

    function unfreeze()
    {
        parent::unfreeze();
        foreach (array_keys($this->_subForm->_elements) as $key) {
            $this->_subForm->_elements[$key]->unfreeze();
        }
    }

    function setPersistantFreeze($persistant = false)
    {
        parent::setPersistantFreeze($persistant);
        foreach (array_keys($this->subForm->_elements) as $key) {
            $this->_subForm->_elements[$key]->setPersistantFreeze($persistant);
        }
    }

    function exportValue(&$submitValues, $assoc = false)
    {
        return $this->_subForm->exportValues();
    }

    function setParentForm(&$form)
    {
		// apparently this is set by the rules code?
        $this->_parentForm =& $form;
        $this->_parentForm->addFormRule(array(&$this, 'checkSubFormRules'));
        $this->_ruleRegistered = true;
    }

    /**
     * If set, the pre validation callback will be called before the sub-form's
validation is checked.
     * This is meant to allow the developer to turn off sub-form validation for
optional forms.
     */
    function setPreValidationCallback($callback = null) {
        $this->_preValidationCallback = $callback;
    }

    function checkSubFormRules($values)
    {
         if ((!isset($this->_preValidationCallback)
              || !is_callable($this->_preValidationCallback)
              || call_user_func($this->_preValidationCallback, $values)) &&
			 !$this->_subForm->validate()) 
		{
            return array($this->getName() => 'Please fix the errors below');
        } else {
            return true;
        }
    }

    /**
     * Sets this element's name
    * @param string name
     */
    function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Gets this element's name
     *
     * @return string name
     */
    function getName()
    {
        return $this->_name;
    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string  Name of event
     * @param     mixed   event arguments
     * @param     object  calling object
     * @access    public
     * @return    bool    true
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        if (is_a($caller, 'html_quickform')) {
            $this->setParentForm($caller);
        }

        switch ($event) {
        case 'updateValue':
            $this->_subForm->_submitValues = $caller->_submitValues;
            $this->_subForm->setDefaults($caller->_defaultValues);
            $this->_subForm->setConstants($caller->_constantValues);
            break;
        default:
            parent::onQuickFormEvent($event, $arg, $caller);
            break;
        }
        return true;
    }
}

if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerElementType('subForm', 
										'lib/subform.php',
										'HTML_QuickForm_SubForm');
}

?>
