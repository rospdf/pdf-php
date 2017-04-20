<?php
require '../src/CpdfExtension.php';

use ROSPDF\Cpdf;
use ROSPDF\CpdfExtension;
use ROSPDF\CpdfLineStyle;

$pdf = new CpdfExtension(Cpdf::$Layout['A4']);

$pdf->Compression = 0;
$pdf->CURPAGE->SetBackground(array(0.7, 0.7, 0.2), 'images/bg.jpg', 'left', 'top', '100%', '100%');

// use Appearance object for images and drawings
$app = $pdf->NewAppearance();

$app->AddOval(100, 700, 35, 1, 0);
$app->AddLinesInCircle(300, 700, 50, 32, 0, 180);
$app->AddLinesInCircle(500, 700, 50, 16, 0, 360);

$app->AddPolyInCircle(100, 500, 50, 6, 0, 360);
$app->AddPolyInCircle(300, 500, 50, 6, 0, 225);
$app->AddLinesInCircle(500, 500, 50, 32, 45, 360);

$app->AddOval(100, 300, 35, 0.5, 0);
$app->AddOval(300, 300, 35, 0.5, 90);
$app->AddOval(500, 300, 35, 0.5, 45);

$app->AddPolygon(100, 200, array(150, 200, 200, 150, 125, 50, 90, 100, 80, 150, 100, 200), true, true, new CpdfLineStyle(6, 'round', 'round'));

$pdf->Stream();
