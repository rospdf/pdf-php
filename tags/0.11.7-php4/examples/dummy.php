<?php
$ext = '../extensions/CezDummy.php';
if(!file_exists($ext)){
	die('This example requires the CezDummy.php extension');
}

include $ext;
$pdf = new CezDummy("a4");
// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false){
    $pdf->tempPath = 'E:/xampp/xampp/tmp';
}

$pdf->selectFont('../src/fonts/Helvetica.afm');

$pdf->ezText("Check the CezDummy.php extension to find the data being displayed\n");
$pdf->ezText("<C:dummy:0>");
$pdf->ezText("<C:dummy:1>");

$pdf->ezStream();
?>