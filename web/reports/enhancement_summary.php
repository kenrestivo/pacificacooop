<?php

	#  Copyright (C) 2004-2005  ken restivo <ken@restivo.org>
	# 
	#  This program is free software; you can redistribute it and/or modify
	#  it under the terms of the GNU General Public License as published by
	#  the Free Software Foundation; either version 2 of the License, or
	#  (at your option) any later version.
	# 
	#  This program is distributed in the hope that it will be useful,
	#  but WITHOUT ANY WARRANTY; without even the implied warranty of
	#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	#  GNU General Public License for more details. 
	# 
	#  You should have received a copy of the GNU General Public License
	#  along with this program; if not, write to the Free Software
	#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

//$Id$

require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopMenu.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');



//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();


$atd = new CoopView(&$cp, 'families', $none);

$menu =& new CoopMenu(&$cp);
print $menu->topNavigation();


print "\n<hr /></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div id="centerCol">';

/// XXX DUPLICATE OF THE SOLICIT SUMMARY ONE!!
/// immediately either create a coop report class, or use the chooser in coopobj
function schoolYearChooser(&$atd, $table)
{ 
    $res ='';
    
    $at = new CoopView(&$atd->page, $table, $none);
 
    $syform =& new HTML_QuickForm('schoolyearchooser', false, false, 
                                  false, false, true);
    $el =& $syform->addElement('select', 'gschoolyear', 'Choose School Year', 
                               //TODO check ispermittedfield for allyears!
                               $at->getSchoolYears(null, true),
                               array('onchange' =>'this.form.submit()'));

    if($sid = thruAuthCore($at->page->auth)){
        $syform->addElement('hidden', 'coop', $sid); 
    }

    $syform->setDefaults(array('gschoolyear' => $at->page->currentSchoolYear));

    $res .= $syform->toHTML();
    
    $foo = $el->getValue();
    $schoolyear=$foo[0];

    return array($schoolyear, $res);
}





function viewHack(&$atd)
{

    $res = '';
	// so they can easily jump here to add stuff.

// 	$res .= sprintf("To take action on %s, click below:", 
// 		$enhancement_hour_callbacks['description']);
// 	actionButtons($auth, $p, $u, $u['family_id'], 
// 				  $enhancement_hour_callbacks, 1);

//    $res .= "<p>This report may take a long time to load</p>";


    list($year, $chooser) = schoolYearChooser(&$atd, 'enhancement_hours');

    $res .= $chooser;


			  
	$listq = mysql_query("
		select families.name, enrollment.am_pm_session,
			families.family_id , families.phone
				from families 
				   left join kids on kids.family_id = families.family_id
				   left join enrollment on kids.kid_id = enrollment.kid_id
				where enrollment.school_year = '$year' 
					and (enrollment.dropout_date is null 
                        or enrollment.dropout_date < '1950-01-01')
			group by families.family_id
			order by enrollment.am_pm_session, families.name
	");

	$err = mysql_error();
	if($err){
		user_error("checkLink(): [$listq]: $err", E_USER_ERROR);
	}



	$tab = new HTML_Table();	

    //hack just to get semester
    // TODO: let them choose it?
    $en =& new Enhancement(&$atd->page, 1);
	$sem = $en->guessSemester();


	$form =& new HTML_QuickForm('htmlsucks');
	$form->addElement('select', 'semester_html_sucks', 'Choose Semester:',
					  array('fall'=> 'Fall', 'spring' => 'Spring'),
					  'onchange="this.form.submit()"');
	// semester is semester(from popup), or semesterhtmlsucks, or default
	$sem = $_REQUEST['semester_html_sucks'] ? 
		$_REQUEST['semester_html_sucks'] : $sem; 
	$sem = $_REQUEST['semester'] ? $_REQUEST['semester'] : $sem; 
	$form->setDefaults(array('semester_html_sucks' =>$sem));

	// my hidden tracking stuff
	if($sid = thruAuthCore($atd->page->auth)){
		$form->addElement('hidden', 'coop', $sid); 
	}
	
	$res .= $form->toHTML();

	nagOnlyButton($showall, 'families', "semester=$sem");

    $tab->addRow(array(
                     'Family Name',
                     sprintf('Hours Completed %s',
                             $sem == 'spring' ? 
                             "<br />(or carried over from fall)" : ""),
                     "Hours Required",
                     "Remaining Hours Owed",
                   'Session',
                     'Phone Number',
                     'Actions'),
                 ' style="tableheader"', "TH");
	while($row = mysql_fetch_array($listq)){
        //confessArray($row,'row');
        $en =& new Enhancement(&$atd->page, $row['family_id']);
		//$en->cp->debug = 6; // XXX test!
        $total = $en->realHoursDone($sem);

        $needed =  $en->owed[$sem] - $total;
        // TODO: if nagonly, show only the needed > 0's
        if($needed > 0 || $showall){
            $atd->obj->family_id = $row['family_id']; // XXX recordbuttons HACK!
            $tab->addRow(array(
                             $row['name'],
                             $total,
                             $en->owed[$sem],
                             $needed >0 ? $needed : "",
                             $row['am_pm_session'],
                             $row['phone'],
                             $atd->recordButtons($row)));
        }
    }

    $tab->altRowAttributes(1, 'class="altrow1"', 
								   'class="altrow2"');

	$res .= "<h2>Totals for $sem $year </h2>";

    $res .= $tab->toHTML();



	return $res;
	 
}

// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
	 
//// EDIT AND NEW //////
 case 'new':
 case 'edit':
	 break;

 case 'details':
     //XXX FIX THIS
     print "bringing this back soon... thanks for waiting";
     break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$atd);

	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END SOLICITSUMMARY -->

