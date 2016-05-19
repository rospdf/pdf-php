<?php
require '../src/CpdfExtension.php';

use ROSPDF\Cpdf;
use ROSPDF\CpdfColor;
use ROSPDF\CpdfBorderStyle;


$pdf = new CpdfExtension(Cpdf::$Layout['A4']);
//Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_OUTPUT;

$t = $pdf->NewText();
$t->AddText("// IMPORTANT NOTE about Annotations");
$t->AddText("// Some viewers DO NOT DISPLAY annotations at all (for instance Chromes PDF Viewer");

// IMPORTANT NOTE about Annotations
// Some viewers DO NOT DISPLAY annotations at all (for instance Chromes PDF Viewer)
// Also the behaviors might differ

$annot = $pdf->NewAnnotation('text', array(50,600,200,800), null, new CpdfColor(array(0.7,0.7,0.5)));
$annot->SetText('Hello World Icon', 'Icon Annot');
$borderstyle = new CpdfBorderStyle(1, 'dash', array(1,3));
$annot2 = $pdf->NewAnnotation('freetext', array(350,700,200,800), $borderstyle, new CpdfColor(array(0.7,0.7,0.8)));
$annot2->SetText('Hello World Freetext', 'Freetext Annotation(not displayed)');

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
