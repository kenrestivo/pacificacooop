<?php

chdir('../');

require_once('CoopPDF.php');

class TestPDF extends FPDF
{
    
    function Header()
        {
            $this->SetFont('Times','B',15);
            $this->Cell(0,22,'this is title',1,0,'C');

            //starts the non-header stuff THIS FAR from where i left off
            $this->Ln(200);
        }


    function Footer()
        {
            //this just sucks. everything specified literally!
            $logoheight= 80;
            $logowidth = 100;
            $this->Image('images/round-small-logo.png',
                         504,
                         700,
                         $logoheight);

            $this->SetXY(504-$logowidth,700 +$logoheight - 22);
            $this->SetFont('Times','B',15);
            $this->Cell(100,22,'this is a very long footer',0,0,'R');
        }

}

class TestReport extends CoopPDF
{

    function build()
        {
            $this->fpdf=new TestPDF('P', 'pt', 'Letter');
            $this->fpdf->AliasNbPages(); // just for testing, i'll not often use this
            $this->fpdf->SetMargins(36,36,36,36);
            $this->fpdf->AddPage();
            $this->fpdf->SetFont('Arial','B',16);
            $this->fpdf->Cell(0,22,'Hello at '. date('r'));
            $this->fpdf->Ln();

            $moretext = 'this rules';
            // gestringwidthis braindead. you have to manually add padding!
            $mtw = $this->fpdf->GetStringWidth($moretext) + 6;
            $this->fpdf->Cell($mtw,22,$moretext, 1);

        }
}


$r =& new TestReport($debug);
$r->run();


?> 