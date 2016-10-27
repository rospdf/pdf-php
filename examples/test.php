<?php
error_reporting(E_ALL);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());
date_default_timezone_set('UTC');

$start = microtime(true);
include 'Cezpdf.php';

class Creport extends Cezpdf{
	public function __construct($p,$o){
  		parent::__construct($p, $o,'none',array());
	}
}
$pdf = new Creport('a4','portrait');

// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false){
    $pdf->tempPath = 'C:/temp';
}
// make sure cache is regenerated

$pdf->ezSetCmMargins(1,3,2.5,2.5);
$pdf->selectFont('Times-Roman');

$pdf->ezText("\n\n\n\n\n\n\n\n");
$pdf->ezText("\n\n\n", 12);

$_POST['emisor'] = "Test";
$_POST['cargo_emisor'] = "Test 123";
$_POST['vicerrectorado'] = "Test 456";
$_POST['profesor'] = "Test 456";
$_POST['créditos'] = "Test 456";
$_POST['curso'] = "Test 456";
$_POST['master'] = "Test 456";
$_POST['asignatura'] = "Test 456";


//$pdf->selectFont('Times-Bold');
$txtintro = $_POST['emisor'].", ".$_POST['cargo_emisor']." DEL ".$_POST['vicerrectorado'].",\n\n";
$pdf->ezText($txtintro, 12, array('spacing'=>1.5,'justification'=> 'full'));


//$pdf->selectFont('Times-Roman');
$pdf->ezText("INFORMA:\n\n");

$pdf->ezSetCmMargins(1,3,4,4);
$pdf->ezText("Que según la documentación existente en este Secretariado, <strong>".$_POST['profesor']."</strong> ha impartido docencia durante el curso ".$_POST['curso']." en el Máster Universitario en <b>".$_POST['master']."</b>, impartiendo la asignatura <b><i>\"".$_POST['asignatura']. "\"</b></i>, perteneciente al primer cuatrimestre, con una docencia de ".$_POST['créditos']. " créditos ECTS en la misma.\n", 0, array('spacing'=>1.5,'justification'=> 'full'));
$pdf->ezSetCmMargins(1,3,4,4.3);	
$pdf->ezText("\n\n", 11);	
//$pdf->ezText("Y para que así conste y surta efecto, a petición del interesado/a firmo la presente, en Jaén, ".date('d'). " de ".$mesesEspanyol[date('n')-1]. " de ".date('Y').".", 12, array('spacing'=>1.5,'justification'=> 'full'));
$pdf->ezText("\n", 11);
$pdf->ezText("\n");
$pdf->ezText($_POST['cargo_emisor'],11,array('justification'=> 'center'));
$pdf->ezText("\n\n\n\n\n\n", 11);	
$pdf->ezText($_POST['emisor'],11,array('justification'=> 'center'));


if (isset($_GET['d']) && $_GET['d']){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",$pdfcode);
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  $pdf->ezStream(array('compress'=>0));
}

$end = microtime(true) - $start;
error_log($end . ' o');
?>