<?php

//$Id$

require_once 'HTML/Menu.php';
require_once 'HTML/Menu/DirectTreeRenderer.php';

require_once('shared.inc');

class CoopMenu extends HTML_Menu
{
	var $realms;
	var $page;
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
			$sf = $this->page->indexEverything($everything);
			include('members.inc');
			$members = $this->page->indexEverything($everything);

 
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
	

} // END COOPMENU CLASS



/// KEEP BELOW

?>