<?php
/**
 * Table Definition for enhancement_hours
 */
require_once 'DB/DataObject.php';

class Enhancement_hours extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'enhancement_hours';               // table name
    var $enhancement_hour_id;             // int(32)  not_null primary_key unique_key auto_increment
    var $parent_id;                       // int(32)  
    var $enhancement_project_id;          // int(32)  not_null
    var $work_date;                       // date(10)  binary
    var $hours;                           // real(6)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Enhancement_hours',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    var $fb_fieldsToRender = array (
        'parent_id', 'enhancement_project_id', 'work_date', 'hours'
        );
	var $fb_formHeaderText =  'Enhancement Hours';
	var $fb_linkDisplayFields = array('work_date', 'hours');
	var $fb_fieldLabels = array (
        'enhancement_hour_id' => 'Unique ID',
        'parent_id' => 'Parent',
        'enhancement_project_id' => 'Project',
        'work_date' => 'Date Worked',
        'hours' => 'Hours Worked',
        'school_year'=> 'School Year'
        );

      
    var $fb_shortHeader = 'Hours';

    var $fb_requiredFields = array(
        'enhancement_project_id',
        'hours',
        'work_date',
        'school_year',
        'parent_id'
        );

   var $fb_sizes = array(
     'hours' => 10
   );

     var $fb_joinPaths = array(
         'family_id' => 'parents'
         );



// set hours size = 10

    function fb_linkConstraints(&$co)
        {
            $par = new CoopObject(&$co->page, 'parents', &$co);
            $co->protectedJoin($par);

            $co->constrainFamily();
            $co->constrainSchoolYear();
            $co->orderByLinkDisplay();
            $co->grouper();
        }


    function fb_display_summary(&$co)
        {

            ///{{{ XXXX DUPLICATION WITH BELOW
            $fid =  $co->page->userStruct['family_id'];
            if(!$fid){
                  return; // give up, it's a teacher
            }

            require_once 'COOP/Enhancement.php';

            $en =& new Enhancement(&$co->page, $fid);
            $total = $en->realHoursDone();
            $sem = $en->guessSemester();
            //confessObj($en, 'en');
            
            $needed =  $en->owed[$sem] - $total;
            //}}}} END DUPLICATION

            $res = '';
            if($total <= 0){
                $res .= sprintf("No enhancement hours have been entered for your family yet for %s semester of the %s school year.",
                               $sem, $en->schoolYear);

            }

            if($needed == 0){
                $res .= sprintf("%s Your family has performed %0.02f enhancement hours for the %s semester of the %s year.", 
                                $needed > 0 ? "" : "Congratulations!",
                                $total, $sem, $en->schoolYear );
            } else if ($needed < 0){
                $res .= sprintf(
                        " You have %0.0f hours to carry over to the spring.",
                        -$needed);
            }

            return $res;
             

            
        }


    function fb_display_alert(&$co)
        {

            ///{{{ XXXX DUPLICATION WITH ABOVE
            $fid =  $co->page->userStruct['family_id'];
            if(!$fid){
                return; // give up, it's a teacher
            }

            require_once 'COOP/Enhancement.php';

            $en =& new Enhancement(&$co->page, $fid);
            $total = $en->realHoursDone();
            $sem = $en->guessSemester();
            //confessObj($en, 'en');
            
            $needed =  $en->owed[$sem] - $total;
            //}}}} END DUPLICATION
            
            if($needed > 0){
                return sprintf(" You must perform %0.02f more hours before %s.",
                               $needed,
                               sql_to_human_date($en->cutoffDatesArray[$sem])
                    );
            } else if ($needed == 0){
                return ;
            } 

            
            
            
        }



}
