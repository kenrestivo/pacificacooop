<?php

	#  Copyright (C) 2004  ken restivo <ken@restivo.org>
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


require_once('../includes/first.inc');
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');



//MAIN
//$_SESSION['toptable'] 

//DB_DataObject::debugLevel(2);

$cp = new coopPage( $debug);
print $cp->pageTop();


$atd = new CoopView(&$cp, 'companies', $none);

print $cp->topNavigation();
print $cp->stackPath();

print "\n<hr /></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div class="centerCol">';




function viewHack(&$atd)
{

    $res = '';

    // TODO: a nag only, like in enhancement
    
    $view =& new CoopView(&$atd->page, 'families', &$faketop);
	$view->obj->fb_formHeaderText = 'Parent Ed Attendance';
    $view->obj->fb_fieldLabels= array(
        'name' => 'Co-Op Family',
        'missed_meetings' => 'Missed Meetings',
        'meetings_required' => 'Meetings Required',
        'meetings_attended' => 'Meetings Attended');
    $view->obj->preDefOrder= array_keys($view->obj->fb_fieldLabels);
    $view->obj->fb_recordActions = array('details' => ACCESS_VIEW);      

    $view->schoolYearChooser(); // HACK to make it go fetch the data
    $view->obj->query(
        sprintf(
'select distinct enrolled.family_id, enrolled.name, 
count(distinct calendar_events.calendar_event_id) - round(hours/3) as missed_meetings,
count(distinct calendar_events.calendar_event_id) as meetings_required,
hours/3 as meetings_attended
from calendar_events,
(select distinct families.family_id, families.name, start_date, dropout_date
                    from families
                        left join kids on families.family_id = kids.family_id 
                        left join enrollment on kids.kid_id = enrollment.kid_id 
                    where enrollment.school_year = "%s"
                    and (enrollment.dropout_date < "1900-01-01"
                       or enrollment.dropout_date > "2006-01-06"
                        or enrollment.dropout_date is null)
                    group by families.family_id
                    order by families.name) as enrolled
left join (select sum(hours) as hours, family_id
     from  parent_ed_attendance
     left join parents    on parent_ed_attendance.parent_id = parents.parent_id
     left join calendar_events 
        on parent_ed_attendance.calendar_event_id = calendar_events.calendar_event_id
     where school_year = "%s"
     group by family_id) as attended
on attended.family_id = enrolled.family_id
where school_year = "%s" and event_id = 2
and event_date >= start_date and  
    (event_date <= dropout_date or dropout_date is null)
and event_date <= now()
group by enrolled.family_id
order by enrolled.name
',
        $view->getChosenSchoolYear(),
        $view->getChosenSchoolYear(),
        $view->getChosenSchoolYear()
));
    $res .= $view->simpleTable(false, true);


	return $res;
	 
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
	 print viewHack(&$atd);

	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


