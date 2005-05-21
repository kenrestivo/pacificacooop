<?php

//$Id$

chdir('../'); // XXX for test and/or maint
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('CoopMenu.php');



PEAR::setErrorHandling(PEAR_ERROR_PRINT);

//$debug = 3;

//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
$cp->pageTop();


$menu =& new CoopMenu();
$menu->page =& $cp;				// XXX hack!
print $menu->topNavigation();

print "<p>Job Descriptions</p>";


print $cp->selfURL('View Jobs');
print $cp->selfURL('Create New Job', array('action' => 'new'));
				   
$atd = new CoopView(&$cp, 'job_descriptions', $none);
$atd->recordActions = array('edit' => "Edit",
							'details' => 'Details'); 

function viewHack(&$cp, &$atd)
{
	print 'nothing';
	// return $atd->simpleTable();
			
}


// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
//// EDIT AND NEW //////
 case 'new':
 case 'edit':

	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp, &$atd);
	 break;
}



done ();

////KEEP EVERTHANG BELOW

?>


<!-- END JOBDESCRIPTIONS -->


