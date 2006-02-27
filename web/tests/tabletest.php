<?php

chdir('../');

require_once('CoopPDF.php');
require_once('lib/pdml.php');


class TestPDML extends CoopPDF
{

    function build()
        {
            $fname='/mnt/www/restivo/bc/pdml/'. $_REQUEST['filename'];
            $f = fopen($fname, 'r');
            $data = fread($f, filesize($fname));
            fclose ($f);


            $this->fpdf = new PDML('P','pt','A4'); 
            $this->fpdf->compress=0;
            $this->fpdf->ParsePDML($data);

        }
}


$r =& new TestPDML($debug);
$r->run();


?> 