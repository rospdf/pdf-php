<?php
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
date_default_timezone_set('UTC');

include 'Cezpdf.php';

class Creport extends Cezpdf
{
    public function __construct($p, $o)
    {
        parent::__construct($p, $o, 'none', []);
    }
}
$pdf = new Creport('a4', 'portrait');

$pdf->ezSetMargins(20, 20, 20, 20);
$pdf->openHere('Fit');

$pdf->selectFont('Helvetica');
$pdf->ezText('Text in Helvetica');
$pdf->selectFont('Courier');
$pdf->ezText('Text in Courier');
$pdf->selectFont('Times-Roman');
$pdf->ezText('Text in Times New Roman');
$pdf->selectFont('ZapfDingbats');
$pdf->ezText('Text in zapfdingbats');

if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream(['compress' => 0]);
}
