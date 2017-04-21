<?php
// performance counter
require '../src/CpdfExtension.php';

use ROSPDF\Cpdf;
use ROSPDF\CpdfExtension;

$start = microtime(true);
// A new page parameter can be either a default layout, defined in Cpdf
// or an array three numbers to define a bounding box (Example: array(20, 20,550, 800)) 
$pdf = new CpdfExtension(Cpdf::$Layout['A4']);
//Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_OUTPUT;

$pdf->Compression = 0;

// put text, left justifed
$pdf->NewText()
        ->AddText("A simple example")
        ->AddText("on how to use <strong>method chaining</strong>", 0, 'center') // now into the center of the document
        ->AddImage('center',null,'../ros.jpg')
        ->AddText('for <b>text</b> and <i>images</i>', 0, 'right')
        ->AddText('which can also include callbacks, like <c:alink:www.google.de>external links</c:alink>', 0, 'center');

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');

// performance counter
$end = microtime(true) - $start;
//error_log($end);
