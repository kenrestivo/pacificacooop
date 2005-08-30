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



	function createLegacy($page )
		{
		
			$this->page =& $page;

			// fix prefix, dammit
			preg_match("|/(.+)/|", $_SERVER['PHP_SELF'],$match);
			$prefix = $match[0];
			//print "ADDING [$prefix] to prefix";
			$this->setURLPrefix($prefix);

			// grab the legacy stuff
			// NOTE! you must manually include the things. i don't do it here
			global $sf_everything;
			global $members_everything;
			$sf = $this->page->indexEverything($sf_everything);
			$members = $this->page->indexEverything($members_everything);

			$this->page->indexed_all = array_merge($members, $sf);

			$allelse_nested = $this->nestByRealm($members, 
										  $this->other_realms);
			$sf_nested =array(
								'title' => 'Springfest',
								'sub' => $this->nestByRealm($sf, 
															$this->springfest_realms));
//  			confessArray($allelse_nested, 'allelse');
//  			confessArray($sf_nested, 'sf');

			$heirmenu = array_merge($allelse_nested, array($sf_nested));
							
//			confessArray($heirmenu, "menuarray"); 
			$this->setMenu($heirmenu);

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

	function callbacksToMenu($everything)
		{
			foreach($everything as $key => $cbs){
				$res[$key]['title'] = 
					$cbs['shortdesc'];						
				if(checkMenuLevel($this->page->auth, 
								  $this->page->userStruct, 
								  $cbs, $cbs['fields'])== 0){
					$res[$key]['url'] = htmlentities(
						sprintf('%s%s', $cbs['page'], 
								SID ? "?" .SID :""));


				} else {
					unset($res[$key]['url']);
				}
			}
			//confessArray($menustruct, 'menustruct');
			return $res; 
		}

// 	function getMoney($ie)
// 		{
// 			foreach($ie as $page => $cbs){
// 				if($cbs['maintable'] == 'income'){
// 					$moneymenu[] = array(
// 						'title' => $cbs['shortdesc'],
// 						// TODO the permissions checking!
// 						'url' => sprintf('%s%s', $cbs['page'], 
// 										 SID ? "?" .SID :""));
// 				}
// 				confessArray($moneymenu, 'moneymenu');
// 				return $moneymenu;
// 			}
// 		}

// 	function getRealms($ie)
// 		{
// 			foreach($ie as $key => $cbs){
// 				$realms[] = substr($cbs['realm'], 0, 7);
				
// 			}
// 			$realms = array_unique($realms);
// 			asort($realms);
// 			confessArray($realms, "realmsort");
// 			return $realms;
// 		}


	// XXX this creates bugs. the array indices are supposed to be NUMBERS
	// but, i use the realm as a key, and that fucks it up
	// basically, the whole function needs to be rewritten
	function nestByRealm($ie, $realm_map)
		{
			foreach($realm_map as $realm => $description){
				$res[$realm]['title'] = $description;
				foreach($ie as $key => $cbs){
					// this substring thing is a nasty, awful hack
					if(strncmp($cbs['realm'], $realm, 7) == 0){
						$res[$realm]['sub'][$key]['title'] = 
							$cbs['shortdesc'];
						// TODO: put the menu stuff in here
						if(checkMenuLevel($this->page->auth, 
										  $this->page->userStruct, 
										  $cbs, $cbs['fields'])== 0){
							$res[$realm]['sub'][$key]['url'] = 
								sprintf('%s%s', $cbs['page'], 
										SID ? "?" .SID :"");

						} else {
							unset($res[$realm]['sub'][$key]['url']);
						}
						
					}
				}
			}
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
			$tab->addCol(array($this->page->selfURL(
								   "Back to Main Menu", "action=menu", 
								   "index.php"))
						 ); // TODO: maybe make that backbutton hilighted?
			$tab->addCol(array($this->page->selfURL("Log Out", 
													'action=logout')));
			
			
						 
			$res .= $tab->toHTML();

			return $res;
		}
	
    function createNew()
        {
            //GAH! this sucks doing it iteratively. it won't recurse endlessly
            //i can only go two levels. XXX FIX THIS: rewrite recursively
            $i = 1;
            $rl =& new CoopObject(&$this->page, 'realms', &$nothing);
            $dbname = sprintf('Tables_in_%s', $rl->obj->_database); //NEED LATER
            //$rl->obj->debugLevel(2);
            $rl->obj->whereAdd('meta_realm_id is null');
            $rl->obj->orderBy('short_description asc');
            $rl->obj->find();
            while($rl->obj->fetch()){
                $res[++$i]['title'] = $rl->obj->short_description;
                $tab =& new CoopObject(&$this->page, 'table_permissions',
                                       &$subrl);
                $tab->obj->realm_id = $rl->obj->realm_id;
                $tab->obj->groupBy('table_name');
                $tab->obj->find();
                $j = $i;
                while($tab->obj->fetch()){
                    $res[$j]['sub'][++$i] = 
                        array('title'=> 
                              $tab->obj->table_name,
                              'url' => $tab->obj->table_name);
                }
                
                $subrl =& new CoopObject(&$this->page, 'realms', &$nothing);
                $subrl->obj->meta_realm_id = $rl->obj->realm_id;
                $subrl->obj->orderBy('short_description asc');
                $subrl->obj->find();
                while($subrl->obj->fetch()){
                    $res[$j]['sub'][++$i]['title'] = 
                        $subrl->obj->short_description;
                    $tab =& new CoopObject(&$this->page, 'table_permissions',
                                           &$subrl);
                    $tab->obj->realm_id = $subrl->obj->realm_id;
                    $tab->obj->groupBy('table_name');
                    $tab->obj->find();
                    $k = $j;
                    while($tab->obj->fetch()){
                        $res[$k]['sub'][++$i] = 
                            array('title'=> 
                                  $tab->obj->table_name,
                                  'url' => $tab->obj->table_name);
                    }
                }
            }
  

			$this->setMenu($res);

            //return $res;
            
        }



} // END COOPMENU CLASS



/// KEEP BELOW

?>