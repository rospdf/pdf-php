<?php
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
date_default_timezone_set('UTC');

include 'Cezpdf.php';

$pdf = new Cezpdf('a4', 'portrait');

$pdf->selectFont('Courier');

$fontSize = 15;
$fontFile = realpath('../src/fonts/FreeSerif.ttf');

$text = 'Text using TTF font, added as image';

$extra = [];
$box = imageftbbox($fontSize, 0, $fontFile, $text, $extra);

$width = abs($box[4] - $box[0]);
$height = $box[5];

$imgHeight = 50;
$imgWidth = 400;

$im = imagecreatetruecolor($imgWidth, $imgHeight);

$text_color = imagecolorallocate($im, 233, 14, 91);
imagettftext($im, $fontSize, 0, ($imgWidth - $width) / 2, ($imgHeight - $height) / 2, $text_color, $fontFile, $text);

$pdf->addImage($im, 100, 700, $imgWidth, $imgHeight, 100);

if (isset($_GET['d']) && $_GET['d']) {
    echo "<pre>" . $pdf->ezOutput(true) . "</pre>";
} else {
    $pdf->ezStream();
}
