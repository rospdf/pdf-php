<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

$start = microtime(true);
include 'Cezpdf.php';

class Creport extends Cezpdf{
	function Creport($p,$o){
  		$this->__construct($p, $o,'none',array());
	}
}
$pdf = new Creport('a4','portrait');
// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false){
    $pdf->tempPath = 'C:/temp';
}
// make sure cache is regenerated
$pdf->cacheTimeout = 0;

// used for Pound sign
$pdf->targetEncoding = 'ISO-8859-1';
// used for Euro and Pound sign
$pdf->targetEncoding = 'cp1252';

$pdf->ezSetMargins(20,20,20,20);
$pdf->openHere('Fit');

$pdf->selectFont('Helvetica');
$result = '';
for($i = 1; $i <= 50; $i++){
    $result.='Lorem ipsum dol sit ';
}

$pdf->addTextWrap(100, 600,10,$result, 250, 'full');

if (isset($_GET['d']) && $_GET['d']){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  $pdf->ezStream(array('compress'=>0));
}

$end = microtime(true) - $start;
error_log($end . ' o');
?>