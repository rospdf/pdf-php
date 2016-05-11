<?php
error_reporting(E_ALL);
set_time_limit(180);

include '../src/Cpdf.php';

$pdf = new Cpdf_Extension(Cpdf_Common::$Layout['A4']);
// to test on windows xampp
if (strpos(PHP_OS, 'WIN') !== false) {
    Cpdf::$TempPath = 'D:/xampp/tmp';
}

$textObject = $pdf->NewText();

// define FreeSerif as Unicode
$textObject->SetFont('FreeSerif', 10, '', true);
$content = file_get_contents("utf8.txt");
$textObject->AddText($content);

// TODO: fix bug for RTL fonts as they cause an error in sprintf, line 2531 for Cpdf.php

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
