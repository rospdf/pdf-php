<?php
error_reporting(E_ALL);
set_time_limit(180);

include '../src/Cezpdf.php';

$pdf = new Cezpdf('a4');
$pdf->Compression = 0;
if(strpos(PHP_OS, 'WIN') !== false)
	Cpdf::$TempPath = 'D:/xampp/tmp';
	
//Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_ALL;

$cols = array(
	'num' => 'AAAB',
	'title' => 'AAAB',
	'mood' => 'AAAB'
);

$options = array(
				'fontSize' => 12,
				'shaded' => 1,
				'showHeadings' => 1,
				/*'xOrientation' => 'right',*/
				'width'=> 380,
				'showBgCol' => 1, 
				'shadeHeadingCol' => array(0.6,0.6,0.5),
				'gridlines'=> Cpdf_Table::DRAWLINE_ROW | Cpdf_Table::DRAWLINE_HEADERROW);
$options['cols'] = array(
	'num' => array('justification'=>'right'/*, 'bgcolor'=> array(0.5,0.8,0.8)*/), 
	'title' => array('justification'=>'right', 'bgcolor'=> array(0.2,0.7,0.8)),
	'mood' => array('justification'=>'right', 'bgcolor'=> array(0.5,0.7,0.6))
);


function generateRandomString($length = 10, $withSpace = false) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
    	
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
		if($withSpace && $i > 0 && !($i % 5)){
			$randomString.= ' ';
			//$i++;
		}
    }
    return $randomString;
}


$data = array();
for ($i=0; $i <75; $i++) { 
	array_push($data, array('num' => rand(100, 999), 'title'=> generateRandomString(12, true), 'mood' => generateRandomString(4)));
}


$pdf->selectFont('Helvetica');
$pdf->ezTable($data, $cols, 'Hello World', $options);

$pdf->ezText("AWESOME", 35, array('justification'=>'center'));

$pdf->ezStream(array('filename'=> basename(__FILE__, '.php').'.pdf'));

?>