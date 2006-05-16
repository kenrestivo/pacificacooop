<?php

//$Id$

/*
	Copyright (C) 2004-2006  ken restivo <ken@restivo.org>
	 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details. 
	
	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


require_once 'CoopObject.php';
require_once('HTML/TreeMenu.php');


class CoopMenu 
{
	var $realms;
    var $page; // *sigh* yet another cache of the coop page
	var $renderer;
    var $alertme = array(); // list of tables to check for alerts
    var $vars;
    var $menustruct; // for compatibility with old html menu
    var $tmenu; // ref of TreeMenu object
    var $menustack = array();
    var $icon = 'folder.gif'; // folder.gif? or a better one?
    var $expandedIcon = 'folder-expanded.gif'; //  or a better one?



    //constructior
    function CoopMenu(&$page)
        {
            $this->page =& $page;
            $this->tmenu  =& new HTML_TreeMenu();
        }

    function topNavigation()
        {
            // DEPRECATED!
            $this->page->printDebug('WARNING! you are using the old topnav', 0);
            user_error('you are using the old coopmenu topnav which is depreciated', 
                       E_USER_WARNING);
            return $this->page->topNavigation();
        }




    //$i is the running total, MUST propogate it,it must be unique
    //yay, a recursive depth-first tree-transversal function
    function createNew($i = 0, $id = 0)
        {
            $subrl =& new CoopObject(&$this->page, 'realms', &$nothing);
            //$subrl->debugWrap(2);
            if($id){
                $subrl->obj->meta_realm_id = $id;
            }else {
                $subrl->obj->whereAdd('meta_realm_id is null or meta_realm_id < 1'); // ONLY top
            }
            $subrl->obj->orderBy('short_description asc');
            $subrl->obj->find();
            // TODO: optimise by  grabbing all the user/group perms here
            // it's the inner part of the big join query
            // then duplicating functionality of ispermittedfield in loop below
            while($subrl->obj->fetch()){
                $k = ++$i;
                $this->page->printDebug('checking realm ' . 
                                        $subrl->obj->short_description, 
                                            2);
                $res[$k]['title'] = htmlentities($subrl->obj->short_description);
                $res[$k]['help'] = htmlentities($subrl->obj->fb_formHeaderText ? $subrl->obj->fb_formHeaderText : 'No Help Available');
                // first the tables
                $tab =& new CoopObject(&$this->page, 'table_permissions',
                                       &$subrl);
                $tab->obj->realm_id = $subrl->obj->realm_id;
                $tab->obj->groupBy('table_name');
                $tab->obj->find();
                while($tab->obj->fetch()){
                    $i++;
                    $this->page->printDebug('checking perms on table ' . 
                                            $tab->obj->table_name, 
                                            2);
                    // ANd here, compare and contrast the user permsit
                    $co =&new CoopObject(&$this->page, $tab->obj->table_name, 
                                         &$nothing);
                    // do add the tle always, but only url if 
                    $res[$k]['sub'][$i]['title']= 
                        htmlentities($co->obj->fb_shortHeader ? $co->obj->fb_shortHeader : 
                        $tab->obj->table_name);
                    $res[$k]['sub'][$i]['help'] = htmlentities($co->obj->fb_formHeaderText ? $co->obj->fb_formHeaderText : 'No Help Available');
                    // check GROUPLEVEL for menulevel!
                    // XXX this is totally broken! use ispermittedfield!2
                    if($co->perms[NULL]['menu'] >= ACCESS_VIEW &&
                        $co->isPermittedField(null, true, true) >= ACCESS_VIEW)
                    {
                        $this->alertme[] = $tab->obj->table_name;
                        $res[$k]['sub'][$i]['url'] = 
                            $this->page->selfURL(
                                array(
                                    'inside' => array('table' => 
                                                      $tab->obj->table_name,
                                                      'realm' => $tab->obj->realm_id),
                                    'base' => $co->obj->fb_usePage ? 
                                    $co->obj->fb_usePage : 'generic.php')); 
                    }
                } // END TABLES
                // NOW GO RECURSE
                list($tmp, $i) = $this->createNew($i, $subrl->obj->realm_id);
                foreach ($tmp as $key => $val){
                    $res[$k]['sub'][$key] = $val;
                }

                //NOW REPORTS
                $tab =& new CoopObject(&$this->page, 'report_permissions',
                                       &$subrl);
                $tab->obj->realm_id = $subrl->obj->realm_id;
                $tab->obj->groupBy('report_name');
                $tab->obj->find();
                while($tab->obj->fetch()){
                    $i++;
                    // do add the tle always, but only url if 
                    $res[$k]['sub'][$i]['title']= htmlentities($tab->obj->report_name);
                    $res[$k]['sub'][$i]['help']= htmlentities($tab->obj->report_name);
                    // TODO: titles for reports
                    $rp =& new CoopObject(&$this->page, 'report_permissions',
                                       &$subrl);
                    if($this->getReportPerms(&$rp, $tab->obj->realm_id) >= ACCESS_VIEW)
                    {
                        $res[$k]['sub'][$i]['url'] = 
                            $this->page->selfURL(
                                array(
                                    'inside' =>
                                    array('table' => 
                                          $tab->obj->table_name,
                                          'realm' => $tab->obj->realm_id),
                                    'base' =>$tab->obj->page)); 
          
          }
                } // END REPORTS

            } // END REALM

            
            //it's recursive. only return at top
            if(!$id){
                $this->page->confessArray($res, 'res', 4);
                $this->vars['menu'] = $res;
                $this->vars['stamp'] = date('U');
                $this->setMenu($res);
                /// don't need to return because recursion will stop here anyway
            }
            
            //remember, it is recursive!
            return array(&$res, $i);
        }

    // maybe create a report class, which is a subclass of coopview
    function getReportPerms(&$rp, $realmid)
        {
            $rp->obj->query(sprintf('
select 
report_permissions.report_name, report_permissions.page,
max(if((upriv.max_group >= report_permissions.menu_level or
report_permissions.menu_level is null or report_permissions.menu_level < 0), 
upriv.max_group, NULL)) as cooked_menu
from report_permissions 
left join 
(select max(user_level) as max_user, max(group_level) as max_group, 
max(year_level) as max_year,
%d as user_id, realm_id
from user_privileges 
where user_id = %d 
or ((user_id < 1 or user_id is null) and group_id in 
(select group_id from users_groups_join 
where user_id = %d)) 
group by realm_id 
order by realm_id) as upriv
on upriv.realm_id = report_permissions.realm_id
where user_id = %d and report_permissions.realm_id = %d
group by report_permissions.realm_id',
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $this->page->auth['uid'],
                                      $realmid
                                      ));
            $res = $rp->obj->getDatabaseResult();
            while ($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $menu  = $row['cooked_menu'];
                $this->page->confessArray($row, 'getReportPerms', 2);
            }
            return $menu;
        }

    // get it from the session cache, if present AND current
    // this will wrap createmenu
    function getMenu()
        {
            $this->vars =& $_SESSION['cmvars'];

            // get the realm stamp
            $aud =& new CoopObject(&$this->page, 'audit_trail', &$none);
            $aud->obj->query('select unix_timestamp(max(updated)) as menu_changed from audit_trail where table_name in ("table_permissions", "users", "user_permissions", "groups", "users_groups_join", "realms", "report_permissions")');
            $aud->obj->fetch();
            $this->page->printDebug(
                sprintf('CoopMenu::getMenu(): lastchange %d savedstamp %d', 
                        $aud->obj->menu_changed, 
                        $this->vars['stamp']),
                2);

            if($aud->obj->menu_changed < $this->vars['stamp'] &&
                !empty($this->vars['menu']))
            {
                $this->page->printDebug('CoopMenu::getMenu(): using saved',2);
                $this->setMenu($this->vars['menu']);
                return;
            }

            $this->page->printDebug('CoopMenu::getMenu(): recalculating', 2);
            $this->createNew();
        }

    // to mirror old html_menu api
    function setMenu(&$res)
        {
            $this->menustruct =& $res;
        }


    function subcurse(&$item, &$parent)
        {
            // XXX whoops! need to recurse, dude!
            if(!empty($item['sub'])){
                array_push($this->menustack, &$parent);
                foreach($item['sub'] as $id => $subitem){
                    $subnode =& new HTML_TreeNode(
                        array('text' => $subitem['title'],
                              'link' => empty($subitem['url']) ? '' : $subitem['url'],
                              'icon' => $this->icon,
                              'expandedIcon' => $this->expandedIcon,
                              'events' => array('title' => $subitem['help'])));
                    $this->menustack[count($this->menustack) - 1]->addItem($subnode);
                    $this->subcurse(&$subitem, &$subnode);
                }
                array_pop($this->menustack);
            }
            
        }
    
    
    function build()
        {
            $this->getMenu(); // first actually retrieve/build the struct
            
            // toplevels are different. they just are.
            foreach($this->menustruct as $id => $item){
                $node =& new HTML_TreeNode(
                    array('text' => $item['title'],
                          'link' => empty($item['url']) ? '' : $item['url'],
                          'icon' => $this->icon,
                          'expandedIcon' => $this->expandedIcon,
                          'events' => array('title' => $item['help'])));
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

            // ok, finally show it!
            $res = "";
            $res .= $this->page->jsRequireOnce(
                COOP_ABSOLUTE_URL_PATH_PEAR_DATA .
                '/HTML_TreeMenu/TreeMenu.js', 'INCLUDE_TREEMENU_JS');
            $res .= '<div class="menu">';
            $res .= $treeMenu->toHTML() ;
            $res .= '</div>';
            return $res;
        }


    function getListBox()
        {
            $listBox  = &new HTML_TreeMenu_Listbox($this->tmenu);
            return $this->page->jsRequireOnce(COOP_ABSOLUTE_URL_PATH_PEAR_DATA .
                '/HTML_TreeMenu/TreeMenu.js', 'INCLUDE_TREEMENU_JS') .
                $listBox->toHTML();

        }



} // END COOPMENU CLASSr




/// KEEP BELOW

?>