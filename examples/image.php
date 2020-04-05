<?php
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
date_default_timezone_set('UTC');

include 'Cezpdf.php';

class Creport extends Cezpdf
{
    public function __construct($p, $o)
    {
        parent::__construct($p, $o, 'color', [0.8, 0.8, 0.8]);
    }
}
$pdf = new Creport('a4', 'portrait');

$pdf->ezSetMargins(20, 20, 20, 20);

$mainFont = 'Times-Roman';
// select a font
$pdf->selectFont($mainFont);
$size = 12;

$height = $pdf->getFontHeight($size);
// modified to use the local file if it can
$pdf->openHere('Fit');

$pdf->ezText("ROS PDF Image Example\n", 18);

$pdf->ezText('PNG grayscaled', 10);
$pdf->ezImage('images/test_grayscaled.png', 0, 0, 'none', 'right');
$pdf->ezText('PNG grayscaled with alpha channel');
$pdf->ezImage('images/test_grayscaled_alpha.png', 0, 0, 'none', 'right');
$pdf->ezText('PNG true color plus alpha channel #1');
$pdf->ezImage('images/test_alpha.png', 0, 0, 'none', 'right');
$pdf->ezText("PNG indexed:\n\n");
$pdf->ezImage('images/test_indexed.png', 0, 0, 'none', 'right');
$pdf->ezNewPage();
$pdf->ezText("PNG indexed transparent (NOT SUPPORTED):\n\n");
$pdf->ezImage('images/test_indexed_transparent.png', 0, 0, 'none', 'right');
$pdf->ezText('JPEG from an external resource');
$pdf->ezImage('https://github.com/rospdf/pdf-php/raw/master/ros.jpg', 0, 0, 'none', 'right');

$pdf->ezText("GIF image converted into JPG\n\n");
$pdf->ezImage('images/test_alpha.gif', 0, 0, 'none', 'right');

if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream(['compress' => 0]);
}

//error_log($pdf->messages);
;
