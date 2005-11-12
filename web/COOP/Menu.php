<?php

//$Id$

/*
	Copyright (C) 2004-2005  ken restivo <ken@restivo.org>
	 
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


require_once 'HTML/Menu.php';
require_once 'HTML/Menu/DirectTreeRenderer.php';
require_once 'HTML/Menu/DirectRenderer.php';
require_once 'HTML/Table.php';

require_once('shared.inc');

class CoopMenu extends HTML_Menu
{
	var $realms;
	var $page;
	var $renderer;
    var $alertme = array(); // list of tables to check for alerts

    //constructior
    function CoopMenu(&$page)
        {
            parent::HTML_Menu();
            $this->page =& $page;
        }

    function topNavigation()
        {
            // DEPRECATED!
            $this->page->printDebug('WARNING! you are using the old topnav', 0);
            return $this->page->topNavigation();
        }


	function kenRender($type = 'sitemap')
		{
	
			switch($type){
			case 'sitemap':
				$this->renderer =& new HTML_Menu_DirectTreeRenderer();
				$this->renderer->setEntryTemplate(HTML_MENU_ENTRY_ACTIVE, 
												  '{title}');
				$this->render($this->renderer, $type);
				break;
			case 'urhere':
				$this->renderer =& new HTML_Menu_DirectRenderer();
				$this->renderer->setMenuTemplate(
 					'<table border="0">',
 					'</table>');
				//print "HEYY HEEYY";
				$this->render($this->renderer, $type);
				//	confessArray($this->getPath(), "apath");
 				if(count($this->getPath()) < 2){
 					return "";
 				}

			break;
			default:
				return "BROKEN TYPE $type";
				break;
			}
			//confessObj($this , "menures");
			$res .= '<div class="menu">';
			$res .= $this->renderer->toHTML();
			$res .= '</div><!-- end menu class -->';
			return $res;
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
                $res[$k]['title'] = $subrl->obj->short_description;
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
                        $co->obj->fb_shortHeader ? $co->obj->fb_shortHeader : 
                        $tab->obj->table_name;
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
                    $res[$k]['sub'][$i]['title']= $tab->obj->report_name;
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
                  $this->setMenu($res);
            }

            return array($res, $i);
        }

    // maybe create a report class, which is a subclass of coopview
    function getReportPerms(&$rp, $realmid)
        {
            $rp->obj->query(sprintf('
select 
report_permissions.report_name, report_permissions.page,
max(if((upriv.max_group > report_permissions.menu_level or
report_permissions.menu_level is null), 
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



} // END COOPMENU CLASSr




/// KEEP BELOW

?>