<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

include 'Cezpdf.php';

class Creport extends Cezpdf{
	function Creport($p,$o){
  		$this->__construct($p, $o,'color',array(0.2,0.8,0.8));
	}
}
$pdf = new Creport('a4','portrait');
// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false){
    $pdf->tempPath = 'E:/xampp/xampp/tmp';
}

$pdf -> ezSetMargins(20,20,20,20);

$mainFont = 'Times-Roman';
// select a font
$pdf->selectFont($mainFont);
$size=12;

$height = $pdf->getFontHeight($size);
// modified to use the local file if it can
$pdf->openHere('Fit');

$pdf->ezText("PNG grayscaled");
$pdf->ezImage('images/test_grayscaled.png',0,0,'none','right');
$pdf->ezText("PNG grayscaled with alpha channel");
$pdf->ezImage('images/test_grayscaled_alpha.png',0,0,'none','right');
$pdf->ezText("PNG true color plus alpha channel #1");
$pdf->ezImage('images/test_alpha.png',0,0,'none','right');
$pdf->ezText("PNG indexed:\n\n");
$pdf->ezImage('images/test_indexed.png',0,500,'width','right');
$pdf->ezNewPage();
$pdf->ezText("PNG indexed transparent (no transparency supported yet):\n\n");
$pdf->ezImage('images/test_indexed_transparent.png',0,500,'width','right');
$pdf->ezText("PNG true color plus alpha channel #2");
$pdf->ezImage('images/test_alpha2.png',0,0,'none','right');
$pdf->ezText("JPEG from an external resource");
$pdf->ezImage('http://pdf-php.sf.net/pdf-php-code/ros.jpg',0,0,'none','right');

$pdf->ezText("GIF image converted into JPG\n\n");
$pdf->ezImage('images/test_alpha.gif',0,0,'none','right');

if (isset($_GET['d']) && $_GET['d']){
  echo $pdf->ezOutput(TRUE);
} else {
  $pdf->ezStream(array('compress'=>0));
}

//error_log($pdf->messages);
?>