<?php
require '../src/CpdfExtension.php';

use ROSPDF\Cpdf;
use ROSPDF\CpdfExtension;

$pdf = new CpdfExtension(Cpdf::$Layout['A4']);

//$pdf->Compression = 0;
$pdf->FontSubset = true;

$textObject = $pdf->NewText();

// define FreeSerif as Unicode
$textObject->SetFont('FreeSerif', 10, '', true);
$content = file_get_contents("utf8.txt");
$textObject->AddText($content, 0, 'full');

// TODO: fix bug for RTL fonts as they cause an error in sprintf, line 2531 for Cpdf.php

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
