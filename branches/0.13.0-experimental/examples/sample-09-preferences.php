<?php
error_reporting(E_ALL);
set_time_limit(180);

include '../src/Cpdf.php';

$pdf = new Cpdf_Extension(Cpdf_Common::$Layout['A4']);

$pdf->Options->OpenAction($pages->CURPAGE, 'FitH');
$pdf->Options->SetPageLayout('TwoColumnLeft');
$pdf->Options->SetPreferences('HideWindowUI', 'true');
$pdf->Options->SetPreferences('HideToolbar', 'true');
$pdf->Options->SetPreferences('HideMenubar', 'true');

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
?>