<?php
error_reporting(E_ALL);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());
set_time_limit(180);

include 'Cpdf.php';

$time_start = microtime(true);

$pdf = new Cpdf_Extension(Cpdf_Common::$Layout['A4']);

//$pdf->Compression  = 0;

$pdf->FontSubset = true;
//Cpdf::$DEBUGLEVEL = Cpdf_Common::DEBUG_ROWS;

// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false)
    Cpdf::$TempPath = 'D:/xampp/tmp';

$ls = new Cpdf_LineStyle(1, 'butt', 'miter');
$table = $pdf->NewTable(array('ly'=>774, 'ux'=>280), 4, null, $ls,Cpdf_Table::DRAWLINE_HEADERROW);
// disable page break for tables by settings BreakPage to 0 (zero)
//$table->BreakPage = 0;

$table->SetFont('FreeSerif', 10,'',true);

// fit the table when finish
$table->Fit = true;

for ($i=1; $i <= 4; $i+=2) { 
	$table->AddCell("ROW $i", null,array(0.5, 0.7, 0.2));
	$table->AddCell("HOUSTON USA € $");
	$table->AddCell("汉");
	$table->AddCell("лобо");
	
	$table->AddCell("ROW ".($i+1), null,array(0.5, 0.5, 0.8));
	$table->AddCell("HOUSTON UNITED STATES");
	$table->AddCell("Discharge Port");
	$table->AddCell("CALLAO, PERU");
	
}

$table->EndTable();

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');

$time_end = microtime(true);
$time = $time_end - $time_start;
//error_log("$time s");
?>