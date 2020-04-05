<?php

set_time_limit(1800);
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());

include 'Cezpdf.php';

class Creport extends Cezpdf
{
    public function Creport($p, $o)
    {
        $this->__construct($p, $o, 'none', []);
    }
}
$pdf = new Creport('a4', 'portrait');

// IMPORTANT: In version >= 0.12.0 it is required to allow custom tags (by using $pdf->allowedTags) before using it
$pdf->allowedTags .= '|comment:.*?';

$pdf->ezSetMargins(20, 20, 20, 20);

$pdf->selectFont('Helvetica');
$pdf->ezText("Some annotations are only shown in Adobe Reader. Chrome Viewer for instance does not show the icons\n");
// text annotation (also know as comments)
$pdf->ezText("<b>The 'Text' annotation:</b>");
$pdf->ezText("This Example shows how easy it is to put comments like this (<C:comment:Hello World comment text>) in between of some text lines.\n");
$pdf->addComment('Fixed position', "This comment is set to a fixed position by\nusing the addComment method explicitly", 500, 790);
// external links
$pdf->ezText("<strong>The 'External Link' annotation:</strong>");
$pdf->ezText("This is an <c:alink:http://pdf-php.sf.net>external</c:alink> link.\n");
// internal links
$pdf->addDestination('test001', 'FitH', $pdf->y);
$pdf->ezText("<strong>The 'Internal Link' annotation:</strong>");
$pdf->ezText("Followed by an <c:ilink:test001>internal</c:ilink> link which requires to set a destination first. Use \$pdf->addDestination() before adding internal links.\n");

if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream(['compress' => 0]);
}
