<?php 

//$Id$

/*
	Copyright (C) 2005  ken restivo <ken@restivo.org>
	 
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

require_once('CoopObject.php');
require_once('DB/DataObject.php');
require_once('object-config.php');
require_once('utils.inc');


//////////////////////////////////////////
/////////////////////// THANKYOU CLASS
class Enhancement
{
    var $cp; // cache reference to page object
	var $schoolYear; // cache of this year's, um, year.
    var $cutoffDatesArray; // cache fall, spring
    var $familyID; // cache of familyID

	// month number, array of fall hours and spring hours
	var $startDates = array(
		9 => array('fall' => 4, 'spring' => 4),
		10 => array('fall' =>3, 'spring' => 4),
		11 => array('fall' =>2, 'spring' => 4),
		12 => array('fall' =>1, 'spring' => 4),
        1 => array('fall' =>0, 'spring' => 4), 
		2 => array('fall' =>0, 'spring' => 4), // yes, feb is same as jan
		3 => array('fall' =>0, 'spring' => 3),
		4 => array('fall' =>0, 'spring' => 2),
		5 => array('fall' =>0, 'spring' => 1)
		);



	function Enhancement (&$cp, $familyID, $schoolYear = false)
		{
            $this->cp =& $cp;
            $this->familyID = $familyID;
			// guess it and cache it
			$this->schoolYear = findSchoolYear($schoolYear);
            $this->loadCutoffs(); // do i really want this here?
		}


    // TODO: a much more complex function that gets the enrollment
    // for a familyID and gets the start date and then calcs this
    // TODO: i will also have to deal with DROP DATE!
    /// BAH!! i also have to calculate these for each semester!
    /// because of the carryovers!



    // date in sql fmt, pleeze YYYY-MM-DD
    // returns array(fall, spring) hours owed based on this start date
    function getHoursOwed($startdate)
        {
            // much simpler than perl regexps!
            list($year, $month, $day) = explode('-', $startdate);

            // condom
            if(!($year && $month && $day)){
                user_error("Enhancement::getHoursOwed($date) bad date",
                           E_USER_ERROR);
            }
            
            $res = $this->startDates[(int)$month];
            return $res;
        }

    
    // returns array of start/drop dates
    function getStartDropDate()
        {
            /// let's start by finding out start/drop date

            $enrol = new CoopObject(&$this->cp, 'enrollment', &$view);
            $enrol->obj->query(sprintf(
                'select min(start_date) as startdate, 
                        max(dropout_date) as dropdate
                        from enrollment
                                left join kids using (kid_id)
                        where enrollment.school_year = "%s" 
                                and kids.family_id = %d ',
                $this->schoolYear, $this->familyID));
            $enrol->obj->fetch();
            
            $res = array('start' => $enrol->obj->startdate, 
                         'drop' => $enrol->obj->dropdate);
            return $res;

        }

	
    // gets cutoff dates from db and caches them
    function loadCutoffs()
        {
            //fall cutoff
            $co = new CoopObject(&$this->cp, 'calendar_events', &$top);
            $co->obj->school_year = $this->schoolYear;
            $co->obj->event_id = 5; // hard coded fall cutoff
            $co->obj->find(true);
            $this->cutoffDatesArray['fall'] = $co->obj->event_date;
            
            //spring cutoff
            $co = new CoopObject(&$this->cp, 'calendar_events', &$top);
            $co->obj->school_year = $this->schoolYear;
            $co->obj->event_id = 6; // hard coded fall cutoff
            $co->obj->find(true);
            $this->cutoffDatesArray['spring'] = $co->obj->event_date;
        }


    // returns array(fall, spring) hours completed
    function getHoursCompleted()
        {
   
            $co = new CoopObject(&$this->cp, 'enhancement_hours', &$top);
            $co->obj->query(sprintf(
                                'select sum(hours) as total 
                        from enhancement_hours 
                                left join parents using (parent_id)
                        where school_year = "%s" and family_id = %d 
                                and work_date <= "%s" ',
                                $this->schoolYear, $this->familyID,
                                $this->cutoffDatesArray['fall']));
            $co->obj->fetch();
            $res['fall'] = $co->obj->total;
   
            // note spring shouldn't include fall hours. auugh.
            $co = new CoopObject(&$this->cp, 'enhancement_hours', &$top);
            $co->obj->query(sprintf(
                                'select sum(hours) as total 
                        from enhancement_hours 
                                left join parents using (parent_id)
                        where school_year = "%s" and family_id = %d 
                                and work_date <= "%s" 
                                and work_date > "%s"',
                                $this->schoolYear, $this->familyID,
                                $this->cutoffDatesArray['spring'],
                                $this->cutoffDatesArray['fall']));
            $co->obj->fetch();
            $res['spring'] = $co->obj->total;
   
            return $res;
        }

    function sqlToUnix($sqldate)
        {
            list($year, $month, $day) = explode('-', $sqldate);
            // condom
            if(!($year && $month && $day)){
                user_error("Enhancement::sqlToUnix($date) bad date",
                           E_USER_ERROR);
            }
            return mktime(0,0,0,$month, $day, $year);
        }

    // so. fucking. ugly.
    function guessSemester($date = false)
        {
            if(!$date){
                $date = date('Y-m-d');
            }
            
            $datun = $this->sqlToUnix($date);
            foreach($this->cutoffDatesArray as $key=>$val){
                $cutoffsun[$key] = $this->sqlToUnix($val);
            }
            
            foreach(array('fall', 'spring') as $semester){
                if($datun < $cutoffsun[$semester]){
                    return $semester;
                }
            }
            user_error("Enhancement::guessSemester($date): couldn't guess!",
                       E_USER_ERROR);
        }

    // a very common funtion. summarises the REAL hours for this family
    // including carryovers from the previous semester
    function realHoursDone($semester = false)
        {
            if(!$semester){
                $semester = $this->guessSemester();
            }
            ///print "sem= $semester<br>";
            //confessObj($this);
            
            $startdrop = $this->getStartDropDate();
            //confessArray($startdrop, 'stardrop');
            
            $owed = $this->getHoursOwed($startdrop['start']);
            //print "owed $owed<br>";
            //confessArray($owed, 'owed');
            
            $completed = $this->getHoursCompleted($this->familyID);
            //confessArray($completed, 'completed');
            
            if($semester == 'fall'){
                return $completed[$semester];
            }
            if($semester == 'spring'){
                return $completed['fall'] - $owed['fall'] 
                    + $completed[$semester];
            }
            
            user_error("Enhancement::realHoursDone($semester): bad semester",
                       E_USER_ERROR);
        }

} // END ENHANCEMENT CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END ENHANCEMENT -->


