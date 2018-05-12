<?php

$ext = '../extensions/CezDummy.php';
if (!file_exists($ext)) {
    die('This example requires the CezDummy.php extension');
}

include $ext;
$pdf = new CezDummy('a4');

$pdf->selectFont('Helvetica');

$pdf->ezText("Check the CezDummy.php extension to find the data being displayed\n");
$pdf->ezText("<b>IMPORTANT:\nIn version >= 0.12.0 it is required to allow custom tags (by using \$pdf->allowedTags) before using it</b>\n");
$pdf->ezText('<C:dummy:0>');
$pdf->ezText('<C:dummy:1>');

$pdf->ezStream();
