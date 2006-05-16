<?php

chdir('..'); // for tests


require_once("first.inc");
require_once("auth.inc");

require_once("CoopPage.php");
require_once("CoopMenu.php");
require_once("CoopView.php");
require_once('HTML/TreeMenu.php');


printf('<script src="%s/HTML_TreeMenu/TreeMenu.js" language="JavaScript" type="text/javascript"></script>', 
       COOP_ABSOLUTE_URL_PATH_PEAR_DATA);

$icon = 'folder.gif';

$menu  =& new HTML_TreeMenu();

$topnode =& new HTML_TreeNode(array('text' => "First level",
                                 'link' => "/phpwork/environs.php",
                                 'icon' => $icon));
$menu->addItem($topnode); // have to add the toplevels to the menu!
$prev =& $topnode;

$next   = &$prev->addItem(new HTML_TreeNode(array('text' => "Second level",
                                                  'link' => "",
                                                  'icon' => $icon)));
$prev =& $next; // going down one level

$next   = &$prev->addItem(new HTML_TreeNode(array('text' => "Third level",
                                                'link' => "/phpwork/environs.php",
                                                'icon' => $icon)));

$next = &$prev->addItem(new HTML_TreeNode(array('text' => "Fourth level",
                                                'link' => "/phpwork/environs.php",
                                                'icon' => $icon)));
$prev =& $next; // going down one level

$next =& $prev->addItem(new HTML_TreeNode(array('text' => "Fifth level",
                                        'icon' => $icon,
                                        'cssClass' => 'treeMenuBold')));

$topnode->addItem(new HTML_TreeNode(array('text' => "Second level, item 2",
                                        'link' => "/phpwork/environs.php",
                                        'icon' => $icon)));

$topnode->addItem(new HTML_TreeNode(array('text' => "Second level, item 3",
                                        'link' => "/phpwork/environs.php",
                                        'icon' => $icon)));

$topnode =& new HTML_TreeNode(array('text' => "Another First level",
                                 'link' => "/phpwork/environs.php",
                                 'icon' => $icon));

$menu->addItem($topnode); // have to add the toplevels to the menu!


// Create the presentation class for the side menu!
$treeMenu = &new HTML_TreeMenu_DHTML(
    $menu, 
    array('images' => 
          COOP_ABSOLUTE_URL_PATH_PEAR_DATA . '/HTML_TreeMenu/images',
          'defaultClass' => 'treeMenuDefault'));
$treeMenu->printMenu();



// for the page top, quick nav?
$listBox  = &new HTML_TreeMenu_Listbox($menu);
$listBox->printMenu();


/// let's try mine now
$cp =& new CoopPage($debug);
$cp->logIn(); // gotta do that for testing

$menu =& new CoopMenu(&$cp);
$menu->getMenu();

$tmenu  =& new HTML_TreeMenu();
$menustack = array();
foreach($menu->vars['menu'] as $id => $item){
   $node =& new HTML_TreeNode(array('text' => $item['title'],
                                    'link' => $item['url'],
                                    'icon' => $icon));
   $tmenu->addItem($node); // have to add the toplevels to the menu!

   // XXX whoops! need to recurse, dude!
   if(!empty($item['sub'])){
       array_push($menustack, &$node);
       foreach($item['sub'] as $id => $subitem){
           $subnode =& new HTML_TreeNode(array('text' => $subitem['title'],
                                            'link' => $subitem['url'],
                                            'icon' => $icon));
           $menustack[count($menustack) - 1]->addItem($subnode);
       }
       array_pop($menustack);
   }
}

// Create the presentation class for the side menu!
$treeMenu =& new HTML_TreeMenu_DHTML(
    $tmenu, 
    array('images' => 
          COOP_ABSOLUTE_URL_PATH_PEAR_DATA . '/HTML_TreeMenu/images',
          'defaultClass' => 'treeMenuDefault'));
$treeMenu->printMenu();


?> 