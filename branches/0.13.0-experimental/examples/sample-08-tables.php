<?php
error_reporting(E_ALL);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());
set_time_limit(180);

include 'Cpdf.php';

$pdf = new Cpdf_Extension(Cpdf_Common::$Layout['A4']);

$ls = new Cpdf_LineStyle(1, 'butt', 'miter');
$table = $pdf->NewTable(array('ly'=>774, 'ux'=>280), 4, null, $ls,  Cpdf_Table::DRAWLINE_ROW);
// disable page break for tables by settings BreakPage to 0 (zero)
//$table->BreakPage = 0;

// fit the table when finish
$table->Fit = true;

for ($i=0; $i < 2; $i++) { 
	$table->AddCell("Load Port", array(0.5, 0.7, 0.2));
	$table->AddCell("HOUSTON USA € $");
	$table->AddCell("汉");
	$table->AddCell("лобо");
	
	$table->AddCell("AsAA", array(0.5, 0.5, 0.8));
	$table->AddCell("HOUSTON UNITED STATES");
	$table->AddCell("Discharge Port");
	$table->AddCell("CALLAO, PERU");
	
}

$table->EndTable();

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
?>