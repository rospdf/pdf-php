<?php
require '../src/CpdfExtension.php';

use ROSPDF\Cpdf;
use ROSPDF\CpdfExtension;
use ROSPDF\CpdfTable;
use ROSPDF\CpdfLineStyle;

$time_start = microtime(true);

$pdf = new CpdfExtension(Cpdf::$Layout['A4']);

//$pdf->Compression  = 0;

$pdf->FontSubset = true;
//Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_ROWS;

$ls = new CpdfLineStyle(1, 'butt', 'miter');
$table = $pdf->NewTable(['uy'=>480, 'ly'=>420, 'addux'=>-20],2, null, $ls, CpdfTable::DRAWLINE_HEADERROW);
// disable page break for tables by settings BreakPage to 0 (zero)
//$table->BreakPage = 0;

$table->SetFont('FreeSerif', 10, '', true);

// fit the table when finish
$table->Fit = true;

for ($i=1; $i <= 2; $i+=2) {
    $table->AddCell("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. Text is incomplete and cell break should take place here.");
    $table->AddCell("лобо", null, [0.5, 0.7, 0.2]);
    
    $table->AddCell("HOUSTON UNITED STATES");
    $table->AddCell("Discharge Port");
}

$table->EndTable();

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');

$time_end = microtime(true);
$time = $time_end - $time_start;
//error_log("$time s");
