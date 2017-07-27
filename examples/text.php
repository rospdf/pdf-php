<?php
error_reporting(E_ALL);
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
date_default_timezone_set('UTC');

include 'Cezpdf.php';

$pdf = new Cezpdf('a4', 'portrait');

if (strpos(PHP_OS, 'WIN') !== false) {
    $pdf->tempPath = 'C:/temp';
}

$pdf->ezSetMargins(20, 20, 20, 20);

$pdf->selectFont('Courier', '', 1, true);

$pdf->ezText('This line should start at the top', 10, array('justification' => 'left'));
$pdf->ezText('followed by this line', 10, array('justification' => 'right'));

// when moving the below right next to the ezText containing the 'atop' option
// it can have side-effects because of the paging.
$pdf->ezText('2nd page content', 10);

$pdf->ezText("This is the positoning part using <strong>atop</strong>\nLets be sure line breaks do not interrupt things\nBut what about page breaks\nYet another line break\nThis line break causes page three", 10, array('justification' => 'center', 'atop' => 50));


if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream();
}
