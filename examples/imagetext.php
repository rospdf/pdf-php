<?php
error_reporting(E_ALL);
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
date_default_timezone_set('UTC');

include 'Cezpdf.php';

$pdf = new Cezpdf('a4', 'portrait');

$pdf->selectFont('Courier');

$imgHeight = 20;
$imgWidth = 120;
$fontSize = 8;
$resampleFactor = 3;

$im = imagecreatetruecolor($imgWidth * $resampleFactor, $imgHeight * $resampleFactor);
$text_color = imagecolorallocate($im, 233, 14, 91);
imagettftext($im, $fontSize * $resampleFactor, 0, 13 * $resampleFactor, 13 * $resampleFactor, $text_color, '../src/fonts/FreeSans.ttf', 'A Simple Text String');

$pdf->addImage($im, 100, 700, $imgWidth, $imgHeight);

if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream();
}
