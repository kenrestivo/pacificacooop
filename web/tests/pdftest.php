<?php

chdir('../');

require_once('CoopPage.php');
require_once('lib/fpdf.php');

$pdf=new FPDF('P', pt, 'Letter');
$pdf->SetMargins(36,36,36,36);
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(100,22,'Hello at '. date('r'));
$pdf->Ln();
$pdf->Cell(100,22,'this rules');
$pdf->Output();

?> 