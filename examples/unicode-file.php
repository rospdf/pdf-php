<?php
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());

include 'Cezpdf.php';

class Creport extends Cezpdf
{
    public function __construct($p, $o)
    {
        parent::__construct($p, $o, 'none', []);
        $this->isUnicode = true;
        $this->allowedTags .= '|uline';
    }
}

$pdf = new Creport('a4', 'portrait');

$pdf->ezSetMargins(20, 20, 20, 20);

$mainFont = (isset($_GET['font'])) ? $_GET['font'] : 'FreeSerif';

$tmp = array(
    'b' => $mainFont .'Bold',
);

$pdf->setFontFamily('FreeSerif', $tmp);

// select a font and fully embed it
$pdf->selectFont($mainFont);

$content = file_get_contents('utf8.txt');

$pdf->ezText($content, 10, ['justification' => 'full']);

if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream();
}

$end = microtime(true) - $start;
//error_log($end . ' execution in seconds (v0.12.2)');
;
