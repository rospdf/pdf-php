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

$pdf->ezSetMargins(30,30,30,30);
$pdf->openHere('Fit');

$pdf->selectFont('Helvetica');
//$result = 'Lorem ipsum dol sit Lorem ipsum dol sit Lorem ipsum dol sit Lorem ipsum dol sit <b>Lorem ipsum</b> dol sit <strong>Lorem</strong> ipsum dol sit <i>bla bla bla</i> Lorem ipsum dol sit Lorem';

$result = 'There is a directive similar to <i>alink</i>, but designed for linking within the document, this is the <i>ilink</i> callback function.';

$pdf->ezText($result, 12);

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