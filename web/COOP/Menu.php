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

			// grab the legacy stuff
		
			include('everything.inc');	
			include('members.inc');
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

			$this->renderer =& new HTML_Menu_DirectTreeRenderer();
			$this->render($this->renderer, 'sitemap');

		}

	function toHTML()
		{
			$res .= '<span class="menutext"';
			$res .= $this->renderer->toHTML();
			$res .= '</span>';
			return $res;
		}

	function callbacksToMenu($everything)
		{
			foreach($everything as $key => $cbs){
				$res[$key]['title'] = 
					$cbs['description'];						
				if(checkMenuLevel($this->page->auth, 
								  getUser($this->page->auth['uid']), 
								  $cbs, $cbs['fields'])== 0){
					$res[$key]['url'] = $cbs['page'];
				}
			}
			//confessArray($menustruct, 'menustruct');
			return $res; 
		}

	function getMoney($ie)
		{
			foreach($ie as $page => $cbs){
				if($cbs['maintable'] == 'income'){
					$moneymenu[] = array(
						'title' => $cbs['description'],
						'url' => $cbs['page']);
				}
				confessArray($moneymenu, 'moneymenu');
				return $moneymenu;
			}
		}

	function getRealms($ie)
		{
			foreach($ie as $key => $cbs){
				$realms[] = substr($cbs['realm'], 0, 7);
				
			}
			$realms = array_unique($realms);
			asort($realms);
			confessArray($realms, "realmsort");
			return $realms;
		}

	function nestByRealm($ie)
		{
			foreach($this->realm_map as $realm => $description){
				$res[$realm]['title'] = $description;
				foreach($ie as $key => $cbs){
					if(strncmp($cbs['realm'], $realm, 7) == 0){
						$res[$realm]['sub'][$key]['title'] = 
							$cbs['description'];
						// TODO: put the menu stuff in here
						if(checkMenuLevel($this->page->auth, 
										  getUser($this->page->auth['uid']), 
										  $cbs, $cbs['fields'])== 0){
							$res[$realm]['sub'][$key]['url'] = $cbs['page'];
						}
					}
				}
			}
			return $res;
		}
	function topNavigation()
		{

			$u = getUser($this->page->auth['uid']);	// ugh.


			$tab =& new HTML_Table;

			$tab->addRow(array(
							 sprintf("<h3>Welcome %s!</h3>", $u['username']),
	

							 $this->page->selfURL("Log Out", 
												  'action=logout')));
			
			
						 
			$res .= $tab->toHTML();
			if(count($this->getPath()) > 1){
///				print $this->getCurrentURL();
				//			confessArray($this->getPath(), "getpath");
				$res .= $this->get('urhere');	
			}

			return $res;
		}
	

	function create(&$page)
		{		
			$this->page =& $page;
 
		//	print "HEY" .  $page->selfURL(false, 'companies[action]=list');
			$heirmenu = array(
				array(
					'title' => 'Solicitation Test',
					'url' => $page->selfURL(
						false, 'tables[companies][action]=list')),
				array(
					'title' => 'Invitations Test',
					'url' => $page->selfURL(
						false, 'tables[invitations][action]=list')));


			$this->setMenu($heirmenu);

			$this->renderer =& new HTML_Menu_DirectTreeRenderer();
			$this->render($this->renderer, 'sitemap');
		}

} // END COOPMENU CLASS



/// KEEP BELOW

?>