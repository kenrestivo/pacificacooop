<?php

//$Id$

require_once 'HTML/Menu.php';
require_once 'HTML/Menu/DirectTreeRenderer.php';

require_once('shared.inc');

class CoopMenu extends HTML_Menu
{
	var $realms;
	var $renderer;

	function create($heirmenu )
		{
			
			$this->renderer =& new HTML_Menu_DirectTreeRenderer();
			$this->render($this->renderer);
		}


	function indexEverything($everything)
		{
			foreach ($everything as $thang => $val){
				$val['fields'] = $$val['fields'];
				$indexed_everything[$val['page']] = $val;
	
			}
			//confessArray($indexed_everything, 'indexedeverythinag');
			return $indexed_everything;
		} 

	function callbacksToMenu($everything)
		{
			foreach($everything as $key => $cbs){
				$menustruct[] = array(
					'title' => $cbs['description'],
					'url' => $cbs['page']);
			}
			//confessArray($menustruct, 'menustruct');
			return $menustruct; 
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

	function nestByRealm($ie, $page)
		{
			foreach($this->realms as $realm => $description){
				$res[$realm]['title'] = $description;
				foreach($ie as $key => $cbs){
					if(strncmp($cbs['realm'], $realm, 7) == 0){
						$res[$realm]['sub'][$key]['title'] = 
							$cbs['description'];
						// TODO: put the menu stuff in here
						//checkMenuLevel($page->auth, 
						//getUser($page->auth['uid']), $cbs, $cbs['fields'])
						$res[$realm]['sub'][$key]['url'] = $cbs['page'];
					}
				}
			}
			return $res;
		}



} // END COOPMENU CLASS



/// KEEP BELOW

?>