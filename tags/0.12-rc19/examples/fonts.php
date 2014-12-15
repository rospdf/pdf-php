<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

include 'Cezpdf.php';

class Creport extends Cezpdf{
	function Creport($p,$o){
  		$this->__construct($p, $o,'none',array());
	}
}
$pdf = new Creport('a4','portrait');
// to test on windows xampp
  if(strpos(PHP_OS, 'WIN') !== false){
    $pdf->tempPath = 'E:/xampp/xampp/tmp';
  }
  
$pdf->ezSetMargins(20,20,20,20);
$pdf->openHere('Fit');

$pdf->selectFont('Helvetica');
$pdf->ezText("Text in Helvetica");
$pdf->selectFont('Courier');
$pdf->ezText("Text in Courier");
$pdf->selectFont('Times-Roman');
$pdf->ezText("Text in Times New Roman");
$pdf->selectFont('ZapfDingbats');
$pdf->ezText("Text in zapfdingbats");

if (isset($_GET['d']) && $_GET['d']){
  echo $pdf->ezOutput(TRUE);
} else {
  $pdf->ezStream(array('compress'=>0));
}
?>