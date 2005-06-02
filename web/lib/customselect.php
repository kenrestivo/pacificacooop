<?php

   // $Id$

require_once('HTML/QuickForm/select.php');
class HTML_QuickForm_customselect extends HTML_QuickForm_select
{

	//TODO: add js... loook at example somewhere?


	function toHtml()
		{

			// I don't understand all this render/accept shit. fuck it.
			// sprintf, however, i understand
			list($table, $field) = explode('-', $this->getName());
			
			return sprintf('%s %s<div class="%s" id="div-%s">&nbsp;
				<a href="javascript:void();" id="%s-toggle"
					onClick="toggleSubform(\'%s\',\'%s\')">Add New %s &gt;&gt;</a></div>',
						   $this->_getJs(),
						   parent::toHTML(), // the actual {element}!
						   '',	// TODO: check hidden here
						   $this->getName(),
						   $this->getName(),
						   $field,
						   $table,
						   ''/* TODO: fetch the grouplabel */ );
		}


       function _getJs()
       {
           // Generate the javascript code needed to handle this element
           $js = '';
           if (!defined('HTML_QUICKFORM_CUSTOMSELECT_EXISTS')) {
			   // We only want to include the javascript code once per form
               define('HTML_QUICKFORM_CUSTOMSELECT_EXISTS', true);

               $js .= sprintf('
/* begin javascript for HTML_QuickForm_customselect */
function showHideDiv(div, state)
{
  if(state > 0){
    div.style.visibility = "visible";
    div.style.display = "block";
    div.style.overflow = "auto";
    div.className = "";
  } else {
    div.style.display = "none";
    div.style.overflow = "hidden";
    div.style.visibility = "hidden";
    div.className = "hidden";
  }
}

function toggleSubform(field, table) 
{
   select = document.getElementById("div-" + table + "-" + field);
   passthru = document.getElementById(table + "-subtables-" + field);
   subform = document.getElementById(table + "-" + field + "-subform");
   if(select.className == "hidden") {
	 showHideDiv(select, 1);
	 showHideDiv(subform, 0);
     passthru.value = "1";
   } else {
	 showHideDiv(select, 0);
	 showHideDiv(subform, 1);
     passthru.value = "0";
	 
   }
}
/* end javascript for HTML_QuickForm_customselect */
               ',
							  $this->getName(),
							  $this->getName(),
							  'subform-thing',
							  $this->getName(),
							  'subform-thing');
			   // wrap wrap wrap wrap it up. i'll take it. up the ying-yang.
               $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
				   $js . "//]]>\n</script>";
           }
           return $js;
       }


	   
}

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('customselect',
										'lib/customselect.php', 
										'HTML_QuickForm_customselect');
}


?>