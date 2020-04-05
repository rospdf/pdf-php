<?php
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
date_default_timezone_set('UTC');

include 'Cezpdf.php';

class Creport extends Cezpdf
{
    public function __construct($p, $o, $t, $op)
    {
        parent::__construct($p, $o, $t, $op);
    }
}

$pdf = new Creport('a4', 'portrait', 'color', [0.8, 0.8, 0.8]);

$pdf->ezSetMargins(0, 0, 0, 0);

$mainFont = 'Courier';
// select a font
$pdf->selectFont($mainFont);
$size = 12;

$height = $pdf->getFontHeight($size);
// modified to use the local file if it can
$pdf->openHere('Fit');

$result = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ';

if (empty($_GET['repeat'])) {
    $_GET['repeat'] = 20;
}

if (empty($_GET['justify'])) {
    $_GET['justify'] = 'full';
}

$frequency = 4;

$result = str_repeat($result, intval($_GET['repeat']));
$result = rtrim($result);

if (empty($_GET['disable'])) {
    $parts = preg_split('/\s/', $result);
    $result = '';
    for ($i=0; $i < count($parts); $i++) {
        if (($i % $frequency) == 0) {
            $result .= '<c:color:'.( mt_rand(0.2*10, 1.0*10) / 10 ).','.( mt_rand(0.0*10, 0.2*10) / 10 ).','.( mt_rand(0.2*10, 0.5*10) / 10 ).'>'.$parts[$i].'</c:color> ';
        } else {
            $result .= $parts[$i].' ';
        }
    }
}

$pdf->ezText($result, 12, ['justification' => $_GET['justify']]);

if (isset($_GET['d']) && $_GET['d']) {
    echo '<pre>';
    echo $pdf->ezOutput(true);
    echo '</pre>';
} else {
    $pdf->ezStream(['compress' => 0]);
}
