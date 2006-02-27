<?php

chdir('../');

require_once('CoopPDF.php');


class TestPDML extends CoopPDF
{


    function build()
        {
            $this->template_file = '/mnt/www/restivo/bc/pdml/'. $_REQUEST['filename'];

            // should this go in parent build or output?
            $this->fpdf = new PDML('P','pt','Letter'); 
    
            
        }
}


$r =& new TestPDML($debug);
$r->run();


?> 