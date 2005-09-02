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
	var $springfest_realms = array( 
		'auction' => 'Auctions',
		'flyers' => 'Flyers',
		'invitations' => 'Invitations',
		'money' => 'Family Fees',
		'nag' => 'Reminders',
		'packaging' => 'Packaging',
		'program,' => 'Program',
		'raffle' => 'Raffles',
		'solicit' => 'Solicitation',
		'thankyou' => 'Thank You',
		'tickets' => 'Tickets'
		);
	var $other_realms = array(
		'roster' => 'Membership',
		'jobs' => 'Jobs',
		'enhancement' => 'Enhancement'
		);


    //constructior
    function CoopMenu(&$page)
        {
            parent::HTML_Menu();
            $this->page =& $page;
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


	function topNavigation()
		{

			// i don't user this->page->userStruct
			// since it requires createlegacy adn i may not have that!
			$u = getUser($this->page->auth['uid']);	// ugh.


			$tab =& new HTML_Table('width="100%"');

			$tab->addCol(array(
							 sprintf("<h3>Welcome %s!</h3>", $u['username'])));
			$tab->addCol(array($this->page->selfURL(array(
								   'value' => "Back to Main Menu", 
                                   'inside' =>"action=menu", 
								   'base' => "index.php")))
						 ); // TODO: maybe make that backbutton hilighted?
			$tab->addCol(array($this->page->selfURL(array('value' =>"Log Out", 
													'inside' =>'action=logout'))));
			
			
						 
			$res .= $tab->toHTML();

			return $res;
		}
	


    //$i is the running total, MUST propogate it,it must be unique
    //yay, a recursive depth-first tree-transversal function
    function createNew($i = 0, $id = 0)
        {
            $subrl =& new CoopObject(&$this->page, 'realms', &$nothing);
            //$subrl->obj->debugLevel(2);
            if($id){
                $subrl->obj->meta_realm_id = $id;
            }else {
                $subrl->obj->whereAdd('meta_realm_id is null'); // ONLY top
            }
            $subrl->obj->orderBy('short_description asc');
            $subrl->obj->find();
            // TODO: optimise by  grabbing all the user/group perms here
            // it's the inner part of the big join query
            // then duplicating functionality of ispermittedfield in loop below
            while($subrl->obj->fetch()){
                $k = ++$i;
                $res[$k]['title'] = $subrl->obj->short_description;
                // first the tables
                $tab =& new CoopObject(&$this->page, 'table_permissions',
                                       &$subrl);
                $tab->obj->realm_id = $subrl->obj->realm_id;
                $tab->obj->groupBy('table_name');
                $tab->obj->find();
                while($tab->obj->fetch()){
                    $i++;
                    // ANd here, compare and contrast the user permsit
                    $co =&new CoopObject(&$this->page, $tab->obj->table_name, 
                                         &$nothing);
                    // do add the tle always, but only url if 
                    $res[$k]['sub'][$i]['title']= 
                        $co->obj->fb_shortHeader ? $co->obj->fb_shortHeader : 
                        $tab->obj->table_name;
                    // check GROUPLEVEL for menulevel!
                    if($co->perms[NULL]['menu'] >= ACCESS_VIEW){
                        $res[$k]['sub'][$i]['url'] = 
                            $this->page->selfURL(
                                array(
                                    'inside' => array('table' => 
                                                      $tab->obj->table_name),
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
                    // TODO: check report level!
                    if(1){
                        $res[$k]['sub'][$i]['url'] = 
                            $this->page->selfURL(
                                array(
                                'inside' =>array('table' => 
                                      $tab->obj->table_name),
                                'base' =>$tab->obj->page)); 
                    }
                } // END REPORTS

            } // END REALM

            
            if(!$id){
                $this->page->confessArray($res, 'res', 4);
                $this->setMenu($res);
            }

            return array($res, $i);
        }



} // END COOPMENU CLASS



/// KEEP BELOW

?>