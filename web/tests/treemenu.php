<?php

chdir('..'); // for tests


require_once("first.inc");
require_once("auth.inc");

require_once("CoopPage.php");
require_once("CoopMenu.php");
require_once("CoopView.php");
require_once('HTML/TreeMenu.php');


class Crap 
{
    var $tmenu; // ref of TreeMenu object
    var $menustack = array();
    var $icon = 'folder.gif';
    var $page; // *sigh* yet another cache of the coop page

    function Crap(&$page)
        {
            $this->page =& $page;
            $this->tmenu  =& new HTML_TreeMenu();
        }

    function subcurse(&$item, &$parent)
        {
            // XXX whoops! need to recurse, dude!
            if(!empty($item['sub'])){
                array_push($this->menustack, &$parent);
                foreach($item['sub'] as $id => $subitem){
                    $subnode =& new HTML_TreeNode(
                        array('text' => $subitem['title'],
                              'link' => $subitem['url'],
                              'icon' => $this->icon));
                    $this->menustack[count($this->menustack) - 1]->addItem($subnode);
                    $this->subcurse(&$subitem, &$subnode);
                }
                array_pop($this->menustack);
            }
            
        }
    
    
    function traverse(&$menustruct)
        {
            // toplevels are different. they just are.
            foreach($menustruct as $id => $item){
                $node =& new HTML_TreeNode(array('text' => $item['title'],
                                                 'link' => $item['url'],
                                                 'icon' => $this->icon));
                $this->tmenu->addItem($node); // have to add the toplevels to the menu!
                $this->subcurse(&$item, &$node);
            }
        }
     
    function getDHTML()
        {
// Create the presentation class for the side menu!
            $treeMenu =& new HTML_TreeMenu_DHTML(
                $this->tmenu, 
                array('images' => 
                      COOP_ABSOLUTE_URL_PATH_PEAR_DATA . '/HTML_TreeMenu/images',
                      'defaultClass' => 'treeMenuDefault'));
            return $this->page->jsRequireOnce(COOP_ABSOLUTE_URL_PATH_PEAR_DATA .
                '/HTML_TreeMenu/TreeMenu.js', 'INCLUDE_TREEMENU_JS') . 
                $treeMenu->toHTML();

        }

    function getListBox()
        {
            $listBox  = &new HTML_TreeMenu_Listbox($this->tmenu);
            return $this->page->jsRequireOnce(COOP_ABSOLUTE_URL_PATH_PEAR_DATA .
                '/HTML_TreeMenu/TreeMenu.js', 'INCLUDE_TREEMENU_JS') .
                $listBox->toHTML();

        }


} // end class




/// let's try mine now
$cp =& new CoopPage($debug);
$cp->logIn(); // gotta do that for testing

$menu =& new CoopMenu(&$cp);
$menu->getMenu();

$crap =& new Crap(&$cp);
$crap->traverse(&$menu->vars['menu']);
print $crap->getDHTML();

print $crap->getListBox();


?> 