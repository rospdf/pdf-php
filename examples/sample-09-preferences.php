<?php
error_reporting(E_ALL);
set_time_limit(180);

include '../src/Cpdf.php';

$pdf = new Cpdf_Extension(Cpdf_Common::$Layout['A4']);
// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false)
    Cpdf::$TempPath = 'D:/xampp/tmp';

$pdf->Options->OpenAction($pdf->CURPAGE, 'FitV');
$pdf->Options->SetPageLayout('TwoColumnLeft');
$pdf->Options->SetPreferences('HideWindowUI', 'true');
$pdf->Options->SetPreferences('HideToolbar', 'true');
$pdf->Options->SetPreferences('HideMenubar', 'true');

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
?>