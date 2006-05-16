<?php
require_once('HTML/TreeMenu.php');
require_once('../no_cvs.inc');

printf('<script src="%s/HTML_TreeMenu/TreeMenu.js" language="JavaScript" type="text/javascript"></script>', 
       COOP_ABSOLUTE_URL_PATH_PEAR_DATA);

$icon = 'folder.gif';

$menu  = new HTML_TreeMenu();

$node1 = new HTML_TreeNode(array('text' => "First level",
                                 'link' => "/phpwork/environs.php",
                                 'icon' => $icon));

$foo   = &$node1->addItem(new HTML_TreeNode(array('text' => "Second level",
                                                  'link' => "/phpwork/environs.php",
                                                  'icon' => $icon)));

$bar   = &$foo->addItem(new HTML_TreeNode(array('text' => "Third level",
                                                'link' => "/phpwork/environs.php",
                                                'icon' => $icon)));

$blaat = &$bar->addItem(new HTML_TreeNode(array('text' => "Fourth level",
                                                'link' => "/phpwork/environs.php",
                                                'icon' => $icon)));

$blaat->addItem(new HTML_TreeNode(array('text' => "Fifth level",
                                        'link' => "/phpwork/environs.php",
                                        'icon' => $icon,
                                        'cssClass' => 'treeMenuBold')));

$node1->addItem(new HTML_TreeNode(array('text' => "Second level, item 2",
                                        'link' => "/phpwork/environs.php",
                                        'icon' => $icon)));

$node1->addItem(new HTML_TreeNode(array('text' => "Second level, item 3",
                                        'link' => "/phpwork/environs.php",
                                        'icon' => $icon)));

$menu->addItem($node1);
$menu->addItem($node1);

// Create the presentation class
$treeMenu = &new HTML_TreeMenu_DHTML(
    $menu, 
    array('images' => 
          COOP_ABSOLUTE_URL_PATH_PEAR_DATA . '/HTML_TreeMenu/images',
          'defaultClass' => 'treeMenuDefault'));
$listBox  = &new HTML_TreeMenu_Listbox($menu);

$treeMenu->printMenu();
$listBox->printMenu();


?> 