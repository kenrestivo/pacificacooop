<?php 

//$Id$

/*
	Copyright (C) 2003  ken restivo <ken@restivo.org>
	 
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

require_once("first.inc");
require_once("shared.inc");
require_once("auth.inc");

require_once("roster.inc");

require_once('DB/DataObject.php');
require_once("HTML/Table.php");
require_once 'HTML/QuickForm.php';
require_once('DB/DataObject/FormBuilder.php');
require_once('Pager/Pager.php');

require_once('object-config.php');

//DB_DataObject::debugLevel(5);

function confessObj($obj, $text)
{

    print"<pre>\n======== $text ============\n";
    print htmlentities(print_r($obj, 1));
    print "</pre>";
 
}

//////////////////////////////////////////
/////////////////////// COOP CLASS
class coopPage
{
	var $obj;
	var $build;
	var $auth;
	var $debug;
	var $pager_result_size;
	var $pager_start;
	var $table;

	function coopPage($debug = false)
		{
			$this->debug = $debug;
		}

	// fafactory.
	function setup($table = false )
		{
			// set it here
			$this->table = $table ? $table : $_SESSION['toptable'];
			
			$this->obj =& DB_DataObject::factory ($this->table); // & instead?
			if (PEAR::isError($obj)){
				die ($obj->getMessage ());
			}
//			print_r($this->obj);

		}

 
	function pageTop()
		{

			print '<HTML>
				<HEAD>
						<link rel=stylesheet href="main.css" title=main>
							<TITLE>Data Entry</TITLE>
				</HEAD>
				<BODY>
				<h2>Pacifica Co-Op Nursery School Data Entry</h2>
				';


				$this->confessArray($_REQUEST, "test REQUEST");
				$this->confessArray($_SESSION, "test SESSION");
				$this->confessArray($_SERVER, "test SERVER");


			warnDev();

			user_error("states.inc: ------- NEW PAGE --------", 
					   E_USER_NOTICE);


			$this->auth = logIn($_REQUEST);


			if($this->auth['state'] != 'loggedin'){
				done();
			}



		}


	// grab the legacy includes and index them
	function indexEverything($everything)
		{
			foreach ($everything as $thang => $val){
				$val['fields'] = $$val['fields'];
				$indexed_everything[$val['page']] = $val;
	
			}
			//confessArray($indexed_everything, 'indexedeverythinag');
			return $indexed_everything;
		} 

	function confessArray($array, $message)
		{
			if($this->debug < 1){
				return;
			}
			confessArray($array, $message);
		}


 
	function engine(){
		$_SESSION = $this->mergeTables($_SESSION, $_REQUEST);
		//confessArray($tabarr, "tables");
		foreach($tabarr as $table => $vals){
			$this->setup($table);
				//	print_r($cp);
			// OK copy my dispatcher logic over now
			switch($tabarr[$table]['action']){
			case 'list':
				print $this->listTable();
				break;
			case 'detail':
				print $this->detailForm($vals['id']);
				break;
			}
		}

	} /// end engine
 

	function findTables()
		{
		}
	
	function mergeTables($array, $overrides, $level = 0)
		{
			$this->confessArray($array, "BEFORE merge: level $level");

			foreach($overrides as $key => $val){
				if(array_key_exists($key, $array)){
					if(is_array($val)){
						$array[$key] = 
							$this->mergeTables($array[$key], $val, $level +1);
					} else {
						$array[$key] = $val;
					}
					
				} else {
					$array[$key] = $val;
				}
			}
						

			$this->confessArray($array, "AFTER  merge, level $level");
		   			return $array;
		}


	// TODO: some nifty way to get session vars outta there
	function requestOrSession($itemName){
	}
	
	// fishes the tables out of a request or session

	// HANDL EOBJ STUFF
	function tablePopup()
		{
			//XXX broken. use menu instead anyway? i'd rather.
			$obj =& new DB_DataObject();	// use the $this->obj?
			$obj->databaseStructure();
			global $_DB_DATAOBJECT;
			//confessArray($_DB_DATAOBJECT, "dataobject");

			foreach($_DB_DATAOBJECT['INI']['coop'] 
					as $table => $cols) {
				if(strpos($table, '__keys') == 0)
					$vals[$table] = $table;
			}

			$form =& new HTML_QuickForm('gettable', 'get');
			// $grp[] =& HTML_QuickForm::createElement(
			// 	'text', 'table', 'Browse table:');
			$grp[] =& HTML_QuickForm::createElement(
				'select', 'table', null, $vals);
			$grp[] =& HTML_QuickForm::createElement(
				'submit', null, 'Change');
			$form->addGroup($grp, NULL, 'Browse table:');
			
			return $form->toHTML();
			//print "<hr>";
		}

	function selfURL($value = false, $inside = false)
		{
			$base = $_SERVER['PHP_SELF'];
			 if(($pos = strpos($base, '?')) !== false) {
                $base = substr($base, 0, $pos);
            }
			 if($value){
				 $res .= '<p><a href="';

			 }
			 if($inside){
 				 $res .= sprintf("%s?%s%s",
								$base, $inside,
								SID ? "&" . SID  : "");
			 } else {
				 $res .= $base .  SID ? "?" . SID  : "";
			 }
			 if($value){
				 $res .= sprintf('">%s</a></p>', $value);
			 }
			 return $res;
		}

	function setFormDefaults(&$form)
		{
			
			$sy =& $form->getElement('school_year');
			if($sy && $sy->getValue() == ""){
				$sy->setValue(findSchoolYear());
			}
		}


	function detailForm($id = false )
		{
	
			//print_r($this);
			$id = $id ? $id : $_SESSION[$this->table]['id'];
			$this->obj->get($id);
			$this->obj->fb_submitText = "Save"; // hack. it ignores conf 
            $this->build =& DB_DataObject_FormBuilder::create (&$this->obj);
            //confessObj($this->build, "build");
            $form =& new HTML_QuickForm(); 
			$form->addElement('html', thruAuth($this->auth, 1));
            $this->build->useForm($form);
			$form =& $this->build->getForm();
            //confessObj($form, "form");
  			//$form->freeze();
			$this->setFormDefaults(&$form);
			if($form->validate ()){
				$res = $form->process (array 
									   (&$this->build, 'processForm'), 
									   false);
				if ($res){
					$this->obj->debug('processed successfully', 
								'detailform', 0);
					saveAudit($this->table, $id, $this->auth['uid']);
					// XXX make sure i don't have to unset id's first!
					///  next action
					print "PRICESSING SEUCCSSCUL";
					$_SESSION[$this->table]['action'] = 'list'; 
 			 		header('Location: ' . $this->selfURL());
				}
				echo "AAAAUUUUUUUUUUUGGH!<br>";
			}

			return $form->toHTML();
	
		}


	// get the pager info for the table ->obj
	function calcPager()
		{

			$count = $this->obj->find();
		
			/// pager driver calculations
			for($i = 1; $i <= $count; $i++){
				$pager_item_data[] = $i;
			}
			$pager_parms = array (
				'mode' => 'Sliding',
				'perPage' => 10,
				'urlVar' => $this->table . '_pageID', // does not like heir?
				'sessionVar' => $this->table . '_pageID', 
				'delta' => 2,
                'useSessions' => 1,
                'itemData' => $pager_item_data
				);
			$pager =& new Pager($pager_parms);
			$pager_result_data = $pager->getPageData();
			$pager_links = $pager->getLinks();
			$this->pager_result_size = sizeof($pager_result_data);
			$this->pager_start = array_shift($pager_result_data);
			//confessArray($pager_result_data, "pagerresult");
			//confessArray($pager_links, "pagerlinks");
			
            $res .= sprintf("%d total records found, in %d pages<br>",
                $count, $pager->numPages());
            $res .= $pager_links['all'];
            			
			return $res;
		}


	function listTable($table = false)
		{

			// most have only one key. feel around for primary if not
			$keys = $this->obj->keys ();
			if (is_array ($keys)){
				$primaryKey = $keys[0];
			}
			
			$tab =& new HTML_Table();

			$pagertext = $this->calcPager();	
			$this->obj->limit($this->pager_start - 1, 
							  $this->pager_result_size);
			$this->obj->find();					// new find with limit.

				
			// now the table
			$hdr = 0;
			while ($this->obj->fetch()){
				$ar = array_merge(
					$this->selfURL("Edit", 
							sprintf('%s[action]=detail&%s[id]=%s',
									$this->table, $this->table , 
									$this->obj->$primaryKey)),
					array_values($this->obj->toArray()));


				if($hdr++ < 1){
					$tab->addRow(array_merge("Action", 
											 array_keys(
												 $this->obj->toArray())), 
								 "bgcolor=9999cc", "TH" );
				}
				$tab->addRow($ar);

			}

	


				// this may not actually belong here
			$res .= $this->selfURL('Add New', 
							sprintf('%s[action]=detail', 
									$this->table));
				 
			$res .= $this->selfURL('Close', 
							sprintf('%s[action]=done', 
									$this->table));


			$tab->altRowAttributes(1, "bgcolor=#CCCCC", "bgcolor=white");
			$res .= $tab->toHTML();

//			$res .= "<hr><br>";

			$res .= $pagertext;
		
			return $res;
		}

	

} // END CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END COOP PAGE -->


