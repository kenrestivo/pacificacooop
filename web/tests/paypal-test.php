26868<?php 

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


print '<HTML>
		<HEAD>
				<link rel=stylesheet href="main.css" title=main>
			<TITLE>Data Entry</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School Data Entry</h2>
	';

//DB_DataObject::debugLevel(5);

confessArray($_REQUEST, "test REQUEST");
confessArray($_SESSION, "test SESSION");

///
warnDev();

user_error("states.inc: ------- NEW PAGE --------", E_USER_NOTICE);


$auth = logIn($_REQUEST);


if($auth['state'] != 'loggedin'){
	done();
}


topNavigation($auth,  getUser($auth['uid']));

//////////////////////////////////////////
/////////////////////// COOP CLASS
class coopPage
{
	var $obj;
	var $build;
	var $pager_result_size;
	var $pager_start;
	var $table;

	// constructor.
	function coopPage($table)
		{
			// set it here
			$this->table = $table;

			$this->obj = DB_DataObject::factory ($this->table); // & instead?
			if (PEAR::isError($obj)){
				die ($obj->getMessage ());
			}


		}

	function requestOrSession($itemName){
	}

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

	function detailForm()
		{
			$auth = $_SESSION['auth'];
			$table = $_SESSION['table'];

			$this->obj->get($_SESSION[$this->table]['id']);
			$this->build =& DB_DataObject_FormBuilder::create ($this->obj);
			$form = new HTML_QuickForm($_SERVER['PHP_SELF']); // XXX &
			$form->addElement('html', thruAuth($auth, 1));
			$build->useForm($form);
			$form =& $build->getForm();
			if($form->validate ()){
				$res = $form->process (array 
									   (&$build, 'processForm'), 
									   false);
				if ($res){
					$obj->debug('processed successfully', 
								'detailform', 0);
					$_SESSION['action'] = 'list'; //  next action
					header(sprintf(
							   'Location: %s%s', 
							   $_SERVER['PHP_SELF'], 
							   SID ? "?" . SID  : ""));
				}
				echo "aaauugh!<br>";
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
				'mode' => 'Jumping',
				'perPage' => 10,
				'delta' => 4,
				'itemData' => $pager_item_data
				);
			$pager =& new Pager($pager_parms);
			$pager_result_data = $pager->getPageData();
			$pager_links = $pager->getLinks();
			$this->pager_result_size = sizeof($pager_result_data);
			$this->pager_start = array_shift($pager_result_data);
			//confessArray($pager_result_data, "pagerresult");
			//confessArray($pager_links, "pagerlinks");
			
			$res .= $pager_links['first'];
			$res .= $pager_links['all'];
			$res .= $pager_links['last'];
			
			return $res;
		}

	function linkStuff()
		{
			$this->obj->get(1);
			$this->obj->find();
			$dosdv = $this->build->getDataObjectSelectDisplayValue(
				&$this->obj);
			print "DOSDV $dosdv\n";
		}


	function listTable()
		{

		// most have only one key. feel around for primary if not
			$keys = $this->obj->keys ();
			if (is_array ($keys)){
				$primaryKey = $keys[0];
			}


			$tab =& new HTML_Table();

			$res .= $this->calcPager();	
			$this->obj->limit($this->pager_start - 1, 
							  $this->pager_result_size);
			$this->obj->find();					// new find with limit.

		// now the table
			$hdr = 0;
			while ($this->obj->fetch()){
				$this->build =& DB_DataObject_FormBuilder::create (
					$this->obj);

				$ar = array_merge(
					sprintf('<a href="%s?action=detail&id=%s&table=%s">
						Edit</a><br>',
							$_SERVER['PHP_SELF'], 
							$this->obj->$primaryKey, $this->table),
					array_values($this->obj->toArray()));


				if($hdr++ < 1){
					$tab->addRow(array_merge("Action", 
											 array_keys(
												 $this->obj->toArray())), 
								 "bgcolor=9999cc", "TH" );
				}
				$tab->addRow($ar);

			}

	
			//$this->linkStuff();

				
			$res .= sprintf(
				'<p><a href="%s?action=detail&table=%s">Add new</a></p>', 
				   $_SERVER['PHP_SELF'], $table) ;

			$tab->altRowAttributes(1, "bgcolor=#CCCCC", "bgcolor=white");
			$res .= $tab->toHTML();

//			$res .= "<hr><br>";

		
			return $res;
		}

} // END CLASS


//MAIN
//$_SESSION['table'] = 'income';

$cp =& new coopPage('income');
print $cp->listTable();
done();


////KEEP EVERTHANG BELOW

?>
<!-- END TEST -->


