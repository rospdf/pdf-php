<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

include 'Cezpdf.php';

$pdf = new Cezpdf('a4','portrait');
// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false){
    $pdf->tempPath = 'E:/xampp/xampp/tmp';
}

if(!isset($_GET['nocrypt'])){
	// define the encryption mode (either RC4 40bit or RC4 128bit)
	$user = ( isset($_GET['user']) )?$_GET['user']:'';
	$owner = ( isset($_GET['owner']) )?$_GET['owner']:'';
	
	$mode = (isset($_GET['mode']) && is_numeric($_GET['mode']))?$_GET['mode']:1;
	$pdf->setEncryption($user, $owner, array(), $mode);
}

// select a font
$pdf->selectFont('Times-Roman');
$pdf->openHere('Fit');

$pdf->ezText("This example shows how to crypt the PDF document\n");

$pdf->ezText("\nUse \"?mode=1\" for RC4 40bit encryption\n");
$pdf->ezText("\nUse \"?mode=2\" for RC4 128bit encryption\n");

$pdf->ezText("\nUse \"?nocrypt\" to disable the encryption\n");
$pdf->ezText("\nUse \"?user=password\" to set a user password\n");
$pdf->ezText("\nUse \"?owner=password\" to set a owner password\n");

if(isset($_GET['nocrypt']))
$pdf->ezText("<b>Not encrypt</b> - nocrypt parameter found");


if (isset($_GET['d']) && $_GET['d']){
  echo $pdf->ezOutput(TRUE);
} else {
	if($mode > 1)
		$encMode = "128BIT";
	else if($mode > 0)
		$encMode = "40BIT";
    else
        $encMode = "NONE";
  $pdf->ezStream(array('Content-Disposition'=>"encrypted_".$encMode.(isset($_GET['user'])?"_withUserPW":"").(isset($_GET['owner'])?"_withOwnerPW":""),'attached'=>0));
}
?>