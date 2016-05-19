<?php
require '../src/CpdfExtension.php';

use ROSPDF\Cpdf;
use ROSPDF\CpdfContent;

// performance counter
$start = microtime(true);

$pdf = new CpdfExtension(Cpdf::$Layout['A4']);

//$pdf->Compression = 0;

// Show page numbers - make use of 'repeat' function while paging
$pagerText = $pdf->NewText(array(20,10,575,20));
// allow custom callback 'pager' which is located in CpdfExtension class
$pagerText->AllowedTags .= '|pager';
$pagerText->SetPageMode(CpdfContent::PMODE_REPEAT); // repeat object from being parsed
$pagerText->SetFont('Helvetica', 6, 'b');
$pagerText->AddText("<c:pager>###</c:pager>", null, 'center'); // use a custom callback to display the page number

// TODO: The paging cause wrong Y position, need to get fixed
$textObject = $pdf->NewText();
//$textObject->SetFont('Helvetica', 10);
$textObject->BreakPage = true; // allow/disallow page breaks - default: true
$textObject->BreakColumn = false; // allow/disallow column breaks if it fits to the page - default: false
for ($i = 1; $i <= 2000; $i++) {
    $textObject->AddText("Lorem ipsum dol Lorem ipsum dol Lorem ipsum dol Lorem ipsum dol Lorem ipsum dol $i");
}

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');

// performance counter
$end = microtime(true) - $start;
error_log($end);
