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

//Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_OUTPUT;

$pdf->ezSetMargins(20,20,20,20);

$pdf->selectFont('courier');
//$pdf->ezText("Text in <c:ilink:1>Helvetica</c:ilink> asdfk jadfa");
//$pdf->ezText("Text in Helvetica Lorem ipsum www.bbc-chartering.com", 0, array('aright' => 260));
$pdf->ezText("Text in <b>Helvetica</b> Lorem <i>ipsum dolor sit bla</i> bla BLAAAAA", 0, array('aright' => 240));
//$pdf->selectFont('Courier');
//$pdf->ezText("Text <c:color:0,1,0>in Cou</c:color>rier VLAL <c:ilink:1>BL ASDFAL</c:ilink> ASDL");


//$pdf->selectFont('Times-Roman');
//$pdf->ezText("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.", 10, array('justification'=>'right'));
//$pdf->selectFont('ZapfDingbats');
//$pdf->ezText("Text in zapfdingbats");

//$pdf->ezNewPage();
//$pdf->openHere('FitV');

if (isset($_GET['d']) && $_GET['d']){
  echo $pdf->ezOutput(TRUE);
} else {
  $pdf->ezStream(array('compress'=>0));
}
?>