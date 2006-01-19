<?php

// $Id$

require_once('HTML/QuickForm/select.php');
require_once('Services/JSON.php');

class HTML_QuickForm_customselect extends HTML_QuickForm_select
{

	var $_parentForm;			// cache
    var $cf; // cache of coopform
    var $field; //cache of short field name
    var $link; //cache of the link array
    var $sub; // cache of sub object
    var $vals; // cache selected values. NEED BECAUSE PHP CAN'T getvalues()[0]!
    var $showEditText = 0; // the edit link show the text USE 1/0 NOT TRUE/FALSE
    var $editperms; // cache of editperms hidden field

    function prepare(&$sub)
        {
            $this->sub =& $sub;
            $this->cf =& $this->_parentForm->CoopForm; // save typing


			list($table, $this->field) = explode('-', $this->getName());


            $this->link = explode(':', $this->cf->forwardLinks[$this->field]);

            list($target, $targfield) = $this->link;
            $target_id =  $target . '-'. $targfield;


            
            //  need these for edit link
            $this->vals = $this->getValue();

            $func = "processCustomSelect(this, '{$target_id}', 
                                                {$this->showEditText})";

            $this->_parentForm->updateElementAttr(
                $this->getName(), 
                array('onkeyup' => $func,
                      'onchange' => $func));



            $this->_addEditPerms();
            $this->_populateEditPerms();
                      
             // TODO: WHEN i figure out how to remove the placeholder b4 saving
//                if(count($this->vals) < 2){
//                    $strHtmlSelected .= '<option value="">None</option>';
//                }


        }

    // editperms is the hidden field with the JSON perms array
    // so it doesn't have to fetch it
    function _addEditPerms()
        {

            $this->editperms =& $this->_parentForm->addElement(
                'hidden',
                'editperms-' . $this->getName(),
                '{}',
                array('id' => 'editperms-' . $this->getName()));
        }


    function _populateEditPerms($chooseone = true)
        {
            $opts = $this->sub->getLinkOptions($chooseone, true);
            // XXX put this in prepare()?
            // it is for customselect, but NOT for searchselect
            $this->loadArray($opts['data']);
            $json = new Services_JSON();// XXX call statically?
            $this->editperms->setValue($json->encode($opts['editperms']));
        }


// XXX broken. doesn't work, and i haven't time to figure out why
//     function getFrozenHTML(){
//         $res .= "";
//         foreach($this->vals as $val){
//             $res .= $this->cf->checkLinkField($this->field, $val);
//         }
//         return $res;
//     }


    function toHtml()
        {

            if ($this->_flagFrozen) {
                return $this->getFrozenHTML();
            } else {

                list($target, $targfield) = $this->link;
                $target_id =  $target . '-'. $targfield;


                /// FINALLY, build the result
                $res = "";
                $res .= $this->_getJs();
                $parent = parent::toHTML(); // the actual {element}!


            
                if($this->sub->isPermittedField(null, true, true) >= ACCESS_EDIT)
                {
                    $tmpsub =& new CoopObject(&$this->cf->page, $target, 
                                              &$this->cf);

                    if($this->vals[0]){
                        // AAUCH! can't use the $this->sub, it's already searched
                        $tmpsub->obj->get($this->vals[0]);
                    }

                    $parent .= '&nbsp;' . $this->cf->page->selfURL(
                        array(
                            'value' =>sprintf(
                                'Edit%s',
                                $this->showEditText ? 
                                ' '.htmlentities($tmpsub->concatLinkFields()) : ''),
                            'par' => false,
                            'elementid' => 'subedit-' . $this->getName(),
                            'tags' => array('class' =>
                                            $tmpsub->isPermittedField() < ACCESS_EDIT ? 'hidden' : ''),
                            'inside' => array('table' => $target,
                                              'action' => 'edit',
                                              $target_id => $this->vals[0],
                                              'push' => $this->getName())));
                }            


                $addnew = "";

                if($this->sub->isPermittedField(null, true, true) >= ACCESS_ADD)
                {
                    //XXX do i really need to wrap it in a div? or just use ID?
                    $addnew = $this->cf->page->selfURL(
                            array(
                                'value' =>sprintf(
                                    'Add New %s &gt;&gt;',
                                    $this->cf->obj->fb_fieldLabels[$this->field]),
                                'par' => false,
                                'inside' => array('table' => $target,
                                                  'action' => 'add',
                                                  'push' => $this->getName())));
                    


                    $this->cf->page->confessArray(
                        $this->cf->obj->fb_putNewFirst,
                        "customsselect {$this->cf->table}-{$this->field}  checking addnew order", 
                        4);
                    
                    // XXX this "algorithm" should be taken out and shot
                    // please make it cleaner and simpler
                    
                    if(!empty($this->cf->obj->fb_putNewFirst) &&
                       in_array($this->field, $this->cf->obj->fb_putNewFirst))
                    {
                        $ord = array($addnew, $parent);
                    } else {
                        $ord = array($parent, $addnew);
                    }
                    $res .= sprintf('<div>%s</div><div>OR&nbsp;%s</div>',
                                    $ord[0], $ord[1] );
                } else {
                    $res .= $parent;
                }
                return $res;
            }
        } //end func toHtml


    function _getJs()
        {
            // Generate the javascript code needed to handle this element
            $js = '';


            // specific to this select
            // rebuild the table/field because i can't use - in globals!
            // XXX this typeof is a hack to get around bug in httpunit
            // the right way would be to make this an onload or something

            // non-specific
            $js .= '
/* begin javascript for HTML_QuickForm_customselect */
function processCustomSelect(selectbox, target_id, showtext)
{
   var edlink = document.getElementById("subedit-" + selectbox.name);
   if(edlink == undefined){
        return;
   }
   var edpermshidden = document.getElementById("editperms-" + selectbox.name);
   if(edpermshidden == undefined){
        return;
   }
    // instead of an onpageload
   if(edpermshidden.decoded == undefined){
       /// must put the fieldname INSIDE the eval when evaling a dict
       eval("edpermshidden.decoded =" + edpermshidden.value);
    }

   if(selectbox.value > 0 && edpermshidden.decoded[selectbox.value]){ 
        edlink.className = "";
        // NOTE the THREE GODDAMNED BACKSLASHES HERE IN THE SOURCE CODE!!
        // to keep PHP from mangling it
        re= new RegExp("(" + target_id + "=)\\\d*?(&)", "g");
        edlink.href = edlink.href.replace(re, "$1" + selectbox.value + "$2");
        if(showtext){
               edlink.innerHTML = "Edit " + 
                        selectbox.options[selectbox.selectedIndex].text;
        }
   } else {
        edlink.className = "hidden";
   }

}
/* end javascript for HTML_QuickForm_customselect */
               ';
				
            
            
            return wrapJS($js, 'HTML_QUICKFORM_CUSTOMSELECT_EXISTS') . "<noscript><h1>WARNING! This page WILL NOT work without Javascript. You must enable Javascript in your browser first. Sorry about that.</h1></noscript>\n\n";
        }



	   
    } // END CLASS CUSTOMSELECT

// took this code from advmultiselect
if (class_exists('HTML_QuickForm')) {
	HTML_QuickForm::registerElementType('customselect',
										'lib/customselect.php', 
										'HTML_QuickForm_customselect');
}


?>