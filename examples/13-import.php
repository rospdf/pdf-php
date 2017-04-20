<?php

require '../src/Cpdf.php';
require '../src/include/RPDI.php';

use ROSPDF\Cpdf;

 $pdf = new RPDI('sample-04-images.pdf', Cpdf::$Layout['A4']);

if(isset($_GET['debug']))
    Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_OUTPUT;

 $pdf->Compression = 0;
 $pdf->ImportPage(1);
 $t = $pdf->NewText();
 $t->SetFont('Helvetica', 14);
 $t->AddText('Image and Background loaded from imported pdf', 0, 'right');
 $t->AddText('But this text is from the current script', 0, 'right');

// the below does not yet work as it has conflicts with /Im dict located in XObject
$app = $pdf->NewAppearance();
$app->AddImage('right', 'middle', 'images/test.jpg');

 $pdf->Stream('template_test.pdf');