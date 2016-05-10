<?php
error_reporting(E_ALL);
set_time_limit(180);

include '../src/Cpdf.php';

// NewPage parameter can be either a default layout, defined in Cpdf_Common
// or an array three numbers to define a bounding box (Example: array(20, 20, 550, 800))
$pdf = new Cpdf_Extension(Cpdf_Common::$Layout['A4']);
// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false)
    Cpdf::$TempPath = 'D:/xampp/tmp';

$textObject = $pdf->NewText();

if($pdf->Compression <> 0){
	$textObject->AddText("\n<b>This document is compressed</b>");
}

$textObject->AddText("\nUse QUERY STRING '?crypt=<1,2>' to encrypt the document");
$textObject->AddText("\nExtend the QUERY STRING with '&password=<string>' to set a user password");
$textObject->AddText("\nAdditionally set '&owner=<string>' for the owner pasword");

$permission = array('print');

if(isset($_GET['crypt'])){
	$encryptionMode = 1;
	if($_GET['crypt'] == '2'){
		$encryptionMode = 2;
	}
	
	$up = '';
	if(isset($_GET['password'])){
		$up = $_GET['password'];
	}
	
	$op = '';
	if(isset($_GET['owner'])){
		$op = $_GET['owner'];
	}
	
	
	$pdf->SetEncryption($encryptionMode, $up, $op, $permission);
}

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
?>