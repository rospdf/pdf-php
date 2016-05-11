<?php
error_reporting(E_ALL);
set_time_limit(180);

include '../src/Cpdf.php';

$pdf = new Cpdf_Extension(Cpdf_Common::$Layout['A4']);
// to test on windows xampp
if (strpos(PHP_OS, 'WIN') !== false) {
    Cpdf::$TempPath = 'D:/xampp/tmp';
}

$t = $pdf->NewText();
$t->AddText("// IMPORTANT NOTE about Annotations");
$t->AddText("// Some viewers DO NOT DISPLAY annotations at all (for instance Chromes PDF Viewer");

// IMPORTANT NOTE about Annotations
// Some viewers DO NOT DISPLAY annotations at all (for instance Chromes PDF Viewer)
// Also the behaviors might differ

$annot = $pdf->NewAnnotation('text', array(50,600,200,800), null, new Cpdf_Color(array(0.7,0.7,0.5)));
$annot->SetText('Hello World Icon', 'Icon Annot');
$borderstyle = new Cpdf_BorderStyle(1, 'dash', array(1,3));
$annot2 = $pdf->NewAnnotation('freetext', array(350,700,200,800), $borderstyle, new Cpdf_Color(array(0.7,0.7,0.8)));
$annot2->SetText('Hello World Freetext', 'Freetext Annotation(not displayed)');

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
