<?php 

//$Id$

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

    print"<pre>";
    print "======== $text ============\n";
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
	var $pager_result_size;
	var $pager_start;
	var $table;

	// constructor.
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

			confessArray($_REQUEST, "test REQUEST");
			confessArray($_SESSION, "test SESSION");
			confessArray($_SERVER, "test SERVER");
			
///
			warnDev();

			user_error("states.inc: ------- NEW PAGE --------", E_USER_NOTICE);


			$this->auth = logIn($_REQUEST);


			if($this->auth['state'] != 'loggedin'){
				done();
			}


			topNavigation($this->auth,  getUser($this->auth['uid']));

		}


 
	function engine(){
		$tabarr = $this->findTables($_REQUEST);
		//confessArray($tabarr, "tables");
		foreach($tabarr as $table => $vals){
			$this->setup($table);
				//	print_r($cp);
			// OK copy my dispatcher logic over now
			switch($_SESSION[$table]['action']){
			case 'list':
				print $this->listTable();
				break;
			case 'detail':
				print $this->detailForm($vals['id']);
				break;
			}
		}

	} /// end engine


////utility, not inside of class
// XXX thsi function, how do you say in your country? it SUCKS.
	function findTables($haystack)
		{
			$needles = array();
			foreach($haystack as $key => $val){
				//print "table $key vars $val<br>";
				if(is_array($val) && array_key_exists('action', $val)){
					$needles[$key] = $val;
					// override session now
					$_SESSION[$key] = $val;
				}
			}
			foreach($_SESSION as $key => $val){
				if(is_array($val) && array_key_exists('action', $val) &&
					!(is_array ($needles[$key]) && 
					  array_key_exists($needles['action']))){
					$needles[$key] = $val;
				}
			}	
			return $needles;
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
			if($form->validate ()){
				$res = $form->process (array 
									   (&$this->build, 'processForm'), 
									   false);
				if ($res){
					$this->obj->debug('processed successfully', 
								'detailform', 0);
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
			$res .= "$count total records found<br>";

			/// pager driver calculations
			for($i = 1; $i <= $count; $i++){
				$pager_item_data[] = $i;
			}
			$pager_parms = array (
				'mode' => 'Sliding',
				'perPage' => 10,
				'urlVar' => $this->table . '[pageID]',
				'delta' => 2,
				'itemData' => $pager_item_data
				);
			$pager =& new Pager($pager_parms);
			$pager_result_data = $pager->getPageData();
			$pager_links = $pager->getLinks();
			$this->pager_result_size = sizeof($pager_result_data);
			$this->pager_start = array_shift($pager_result_data);
			//confessArray($pager_result_data, "pagerresult");
			//confessArray($pager_links, "pagerlinks");
			
			$res .= $pager_links['all'];
			
			return $res;
		}

	function showCrosslink($id, $idx)
		{
			
            $myobj = $this->obj;
            $myobj->get($id);
            $thisLink = explode(":", $allLinks[$idx]);
            //confessArray ($thisLink, "thislink");
            $damn = $myobj->getLink($thisLink[1]);
            
                //confessArray($damn, "linkobj");
            // ack will need to use fbdisplay
            $gah = "_" . $thislink[0];
            return $damn;
           
        }


	function listTable($table = false)
		{

			// most have only one key. feel around for primary if not
			$keys = $this->obj->keys ();
			if (is_array ($keys)){
				$primaryKey = $keys[0];
			}
			
            print "HEY" . $this->showCrosslink(10, 'family_id' );
			
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


//MAIN
//$_SESSION['toptable'] 


$cp =& new coopPage();
$cp->pageTop();
$cp->engine();

done();


////KEEP EVERTHANG BELOW

?>
<!-- END TEST -->


