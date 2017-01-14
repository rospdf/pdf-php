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
 $t->AddText('Hello World');
 $pdf->Stream('template_test.pdf');