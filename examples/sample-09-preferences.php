<?php
require '../src/CpdfExtension.php';

use ROSPDF\Cpdf;

$pdf = new CpdfExtension(Cpdf::$Layout['A4']);

$pdf->Options->OpenAction($pdf->CURPAGE, 'FitV');
$pdf->Options->SetPageLayout('TwoColumnLeft');
$pdf->Options->SetPreferences('HideWindowUI', 'true');
$pdf->Options->SetPreferences('HideToolbar', 'true');
$pdf->Options->SetPreferences('HideMenubar', 'true');

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
