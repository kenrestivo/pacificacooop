<?php
require_once('HTML/TreeMenu.php');
require_once('../no_cvs.inc');

printf('<script src="%s/HTML_TreeMenu/TreeMenu.js" language="JavaScript" type="text/javascript"></script>', 
       COOP_ABSOLUTE_URL_PATH_PEAR_DATA);

$icon = 'folder.gif';

$menu  = new HTML_TreeMenu();

$topnode =& new HTML_TreeNode(array('text' => "First level",
                                 'link' => "/phpwork/environs.php",
                                 'icon' => $icon));
$menu->addItem($topnode); // have to add the toplevels to the menu!

$foo   = &$topnode->addItem(new HTML_TreeNode(array('text' => "Second level",
                                                  'link' => "",
                                                  'icon' => $icon)));

$bar   = &$foo->addItem(new HTML_TreeNode(array('text' => "Third level",
                                                'link' => "/phpwork/environs.php",
                                                'icon' => $icon)));

$blaat = &$bar->addItem(new HTML_TreeNode(array('text' => "Fourth level",
                                                'link' => "/phpwork/environs.php",
                                                'icon' => $icon)));

$blaat->addItem(new HTML_TreeNode(array('text' => "Fifth level",
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


?> 