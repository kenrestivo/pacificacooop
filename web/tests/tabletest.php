<?php

chdir('../');

require_once('CoopPDF.php');


class TestPDML extends CoopPDF
{

    var $template_file = 'bid-sheet.pdml';


    function build()
        {

            ////////////// BIDSHEETS
            $bidsheets =& new CoopView(&$this, 'packages', &$nothing);
            $pt =& new CoopView(&$this, 'package_types', &$bidsheets);
            $bidsheets->protectedJoin($pt);
            $bidsheets->obj->whereAdd('package_type_short = "Silent"');
            // tal needs this to decide whether to print the increment
            array_push($bidsheets->obj->fb_preDefOrder, 'package_type_short');
            $bidsheets->obj->fb_fieldLabels['package_type_short'] = 'Package Type';
    

            $bidsheets->fullText= 1; // gotta have it

            if(devSite() && $_REQUEST['limit']){
                // XXX TEMPORARY HACK FOR TESTING
                $bidsheets->obj->limit($_REQUEST['limit']);
            }

            $bidsheets->find(true);
            $this->template->setRef('bidsheets', $bidsheets);

            $this->printDebug("sy $sy nav $nav ". $bidsheets->getChosenSchoolYear(), 1);


            $crap = array_fill(0,10,'');
            $this->template->setRef('blanklines', $crap);
            
        }
}


$r =& new TestPDML($debug);
$r->run();


?> 