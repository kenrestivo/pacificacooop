<?php

chdir('../');

require_once('CoopPDF.php');


class TestPDML extends CoopPDF
{

    function TestPDML($debug)
        {
            $this->template_file = '/mnt/www/restivo/bc/pdml/'. $_REQUEST['filename'];
            parent::CoopPDF($debug);
        }


    function build()
        {

            //put some data in
            
        }
}


$r =& new TestPDML($debug);
$r->run();


?> 