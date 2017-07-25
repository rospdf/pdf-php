<?php

error_reporting(E_ALL);
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
date_default_timezone_set('UTC');

include 'Cezpdf.php';

$pdf = new Cezpdf('a4', 'portrait');

// to test on windows xampp
if (strpos(PHP_OS, 'WIN') !== false) {
    $pdf->tempPath = 'C:/temp';
}

$pdf->ezSetMargins(20, 20, 20, 20);

// select a font and use font subsetting
$pdf->selectFont('Courier', '', 1, true);

$pdf->ezText('This line should start at the top', 10, array('justification' => 'left'));
$pdf->ezText('followed by this line', 10, array('justification' => 'right'));

$pdf->ezText("This is the positoning part using <strong>atop</strong>\nLets be sure line breaks do not interrupt things\nBut what about page breaks", 10, array('justification' => 'center', 'atop' => 50));
//$pdf->ezNewPage();
$pdf->ezText('2nd page content', 10);


if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream();
}
