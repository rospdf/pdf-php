<?php
error_reporting(E_WARNING);
set_time_limit(180);

// performance counter
$start = microtime(true);

include '../src/Cpdf.php';

$pdf = new Cpdf_Extension(Cpdf_Common::$Layout['A4']);
// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false)
    Cpdf::$TempPath = 'D:/xampp/tmp';

$pdf->Compression = 0;

// DEBUG: used to draw the Bounding boxes with a red colored line
Cpdf::$DEBUGLEVEL = Cpdf_Common::DEBUG_BBOX;

// DEBUG: used to draw the table border only
Cpdf::$DEBUGLEVEL = Cpdf_Common::DEBUG_TABLE;

// DEBUG: used to draw the table and text border (PoC)
Cpdf::$DEBUGLEVEL = Cpdf_Common::DEBUG_TABLE | Cpdf_Common::DEBUG_TEXT;

// DEBUG: Output error and warning messages into apache error log
Cpdf::$DEBUGLEVEL = Cpdf_Common::DEBUG_MSG_ERR;

// DEBUG OUTPUT (PLAIN TEXT) - requires to set $pdf->Compression = 0
Cpdf::$DEBUGLEVEL = Cpdf_Common::DEBUG_OUTPUT;

// DEBUG everything, except DEBUG_OUTPUT
Cpdf::$DEBUGLEVEL =  (Cpdf_Common::DEBUG_ALL ^ Cpdf_Common::DEBUG_OUTPUT);


$pdf->DefaultFontFamily['gothmbok'] = array('b'=>'GOTHMMED');

$t = $pdf->NewText(array('ly'=> 790));
//$t->SetFont('GOTHMED', 16,'', true);
$t->AddText('Some Title goes here');

$linestyle = new Cpdf_LineStyle(0.5,'butt');
$table = $pdf->NewTable(array('uy'=>770,'ly'=> 650), 4, null, $linestyle, Cpdf_Table::DRAWLINE_ROW);
//$table->SetPageMode(Cpdf_Content::PMODE_ALL, Cpdf_Content::PMODE_ALL);

$table->SetColumnWidths(85,null,85,null);

$m = array('bottom'=>5,'top'=> 5);

$table->SetFont('GOTHMBOK', 10, '', true);
$table->AddCell('<b>Invoice no.</b>', null, $m);
$table->AddCell('XXXX-XXX', null, $m);
$table->AddCell('<b>Date</b>',null, $m); 
$table->AddCell('05/05/2013',null, $m);

$table->AddCell('<b>Client</b>', null, $m);
$table->AddCell('XXX XXXXXXXX XXXXXXXX',null, $m);
$table->AddCell('<b>XXXXX</b>', null, $m);
$table->AddCell('0313', null, $m);

$table->AddCell('<b>Contact person</b>', null, $m);
$table->AddCell('XXXX XXXXXX', null, $m);
$table->AddCell('<b>XXXX</b>', null, $m);
$table->AddCell('XXXX XXXXXXX', null, $m);

$table->AddCell('<b>XXXXX XXX</b>', null, $m);
$table->AddCell('XXXXXX, XXXX XXXX (XXXXX XXXXXXX)', null, $m);
$table->AddCell('<b>XXXX XXXX</b>', null, $m);
$table->AddCell('XXXXX, XXXXX', null, $m);

$table->EndTable();

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');

// performance counter
$end = microtime(true) - $start;
//error_log($end);
?>