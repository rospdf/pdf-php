<?php
require '../src/CpdfExtension.php';

use ROSPDF\Cpdf;
use ROSPDF\CpdfExtension;
use ROSPDF\CpdfTable;
use ROSPDF\CpdfLineStyle;

// performance counter
$start = microtime(true);

$pdf = new CpdfExtension(Cpdf::$Layout['A4']);

$pdf->Compression = 0;

// DEBUG: used to draw the Bounding boxes with a red colored line
Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_BBOX;

// DEBUG: used to draw the table border only
Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_TABLE;

// DEBUG: used to draw the table and text border (PoC)
Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_TABLE | Cpdf::DEBUG_TEXT;

// DEBUG: Output error and warning messages into apache error log
Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_MSG_ERR;

// DEBUG OUTPUT (PLAIN TEXT) - requires to set $pdf->Compression = 0
Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_OUTPUT;

// DEBUG everything, except DEBUG_OUTPUT
Cpdf::$DEBUGLEVEL =  (Cpdf::DEBUG_ALL ^ Cpdf::DEBUG_OUTPUT);


$pdf->DefaultFontFamily['gothmbok'] = array('b'=>'GOTHMMED');

$t = $pdf->NewText(array('ly'=> 790));
//$t->SetFont('GOTHMED', 16,'', true);
$t->AddText('Some Title goes here');

$linestyle = new CpdfLineStyle(0.5, 'butt');
$table = $pdf->NewTable(array('uy'=>770,'ly'=> 650), 4, null, $linestyle, CpdfTable::DRAWLINE_ROW);

$table->SetColumnWidths(85, null, 85, null);

$m = array('bottom'=>5,'top'=> 5);

//$table->SetFont('GOTHMBOK', 10, '', true);
$table->AddCell('<b>Invoice no.</b>', null, $m);
$table->AddCell('XXXX-XXX', null, $m);
$table->AddCell('<b>Date</b>', null, $m);
$table->AddCell('05/05/2013', null, $m);

$table->AddCell('<b>Client</b>', null, $m);
$table->AddCell('XXX XXXXXXXX XXXXXXXX', null, $m);
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
