<?php

//$Id$

/*
	Copyright (C) 2004  ken restivo <ken@restivo.org>
	 
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

require_once('shared.inc');

class CoopMenu extends HTML_Menu
{
	var $realms;
	var $page;
	var $indexed_all; 			// legacy stuff. ALL of the callbacks
	var $renderer;
	var $realm_map = array( 
		'auction' => 'Auctions',
		'flyers' => 'Flyers',
		'invitations' => 'Invitations',
		'money' => 'Family Fees',
		'nag' => 'Reminders',
		'packaging' => 'Packaging',
		'raffle' => 'Raffles',
		'solicit' => 'Solicitation',
		'tickets' => 'Tickets'
		);

	function createLegacy($page )
		{
		
			$this->page =& $page;

			// fix prefix, dammit
			preg_match("|/(.+)/|", $_SERVER['PHP_SELF'],$match);
			$prefix = $match[0];
			//print "ADDING [$prefix] to prefix";
			$this->setURLPrefix($prefix);

			// grab the legacy stuff
		
			global $sf_everything;
			global $members_everything;
			$sf = $this->page->indexEverything($sf_everything);
			$members = $this->page->indexEverything($members_everything);

			$this->indexed_all = array_merge($members, $sf);

			$heirmenu = array(
				array(
					'title' => 'Enhancement',
					'sub' => $this->callbacksToMenu($members, 
													$this->coop_page)),
				array(
					'title' => 'Springfest',
					'sub' => $this->nestByRealm($sf, $this->coop_page)));


			$this->setMenu($heirmenu);

		}


	function kenRender($type = 'sitemap')
		{
	
			switch($type){
			case 'sitemap':
				$this->renderer =& new HTML_Menu_DirectTreeRenderer();
				$this->render($this->renderer, $type);
				$res = $this->renderer->toHTML();
				break;
			case 'urhere':
				$this->renderer =& new HTML_Menu_DirectRenderer();
				$this->renderer->setMenuTemplate(
 					'<span class="menutext"><table border="0">',
 					'</span></table>');
				 //print "HEYY HEEYY";
				$this->render($this->renderer, $type);
				//	confessArray($this->getPath(), "apath");
 				if(count($this->getPath()) < 2){
 					return "";
 				}
				$res = $this->renderer->toHTML();
			break;
			default:
				return "BROKEN TYPE $type";
				break;
			}
			//confessObj($this , "menures");
			return $res;
		}

	function callbacksToMenu($everything)
		{
			foreach($everything as $key => $cbs){
				$res[$key]['title'] = 
					$cbs['shortdesc'];						
				if(checkMenuLevel($this->page->auth, 
								  getUser($this->page->auth['uid']), 
								  $cbs, $cbs['fields'])== 0){
					$res[$key]['url'] = sprintf('%s%s', $cbs['page'], 
												SID ? "?" .SID :"");


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

	function nestByRealm($ie)
		{
			foreach($this->realm_map as $realm => $description){
				$res[$realm]['title'] = $description;
				foreach($ie as $key => $cbs){
					if(strncmp($cbs['realm'], $realm, 7) == 0){
						$res[$realm]['sub'][$key]['title'] = 
							$cbs['shortdesc'];
						// TODO: put the menu stuff in here
						if(checkMenuLevel($this->page->auth, 
										  getUser($this->page->auth['uid']), 
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

			$u = getUser($this->page->auth['uid']);	// ugh.


			$tab =& new HTML_Table('width="100%"');

			$tab->addRow(array(
							 sprintf("<h3>Welcome %s!</h3>", $u['username']),
							 mainMenuForm(),
							 $this->page->selfURL("Log Out", 
												  'action=logout')));
			
			
						 
			$res .= $tab->toHTML();

			return $res;
		}
	


} // END COOPMENU CLASS



/// KEEP BELOW

?>