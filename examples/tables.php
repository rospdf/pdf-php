<?php

date_default_timezone_set('UTC');

include_once '../src/Cezpdf.php';
$pdf = new CezPDF('a4');

$pdf->selectFont('Helvetica');

// some general data used for table output
$data = array(
 ['num' => 0, 'name' => 'gandalf', 'type' => 'wizard'], ['num' => 2, 'name' => 'bilbo', 'type' => 'hobbit', 'url' => 'http://www.ros.co.nz/pdf/'], ['num' => 3, 'name' => 'frodo', 'type' => 'hobbit'], ['num' => 4, 'name' => 'saruman', 'type' => 'bad dude', 'url' => 'http://sourceforge.net/projects/pdf-php'], ['num' => 5, 'name' => 'sauron', 'type' => 'really bad dude'],
);

$cols = ['num' => 'No', 'type' => 'Type', 'name' => '<i>Alias</i>'];
$coloptions = ['num' => ['justification' => 'right'], 'name' => ['justification' => 'left'], 'type' => ['justification' => 'center']];

$pdf->ezText('<b>GRIDLINE</b>', 12);

$pdf->ezText("<b>using 'showLines' option - DEPRECATED</b>\n", 10);

$pdf->ezText("\nDefault: showLines = 1\n", 10);
$pdf->ezTable($data, $cols);

$pdf->ezText("\nDisabled showLines = 0\n", 10);
$pdf->ezTable($data, $cols, '', ['showHeadings' => 0, 'shaded' => 0, 'showLines' => 0]);

$pdf->ezText("\nHorizontal lines (per row) - showLines = 3\n");
$pdf->ezTable($data, $cols, '', ['showHeadings' => 1, 'shaded' => 0, 'showLines' => 3]);

$pdf->ezText("\nHeader line only - showLines = 4\n");
$pdf->ezTable($data, ['type' => 'Type', 'name' => '<i>Alias</i>'], '', ['showHeadings' => 1, 'shaded' => 0, 'showLines' => 4]);

// get all user defined constants starting with 'EZ_GRIDLINE'
$all_constants = get_defined_constants();
$userConstants = [];
foreach ($all_constants as $k => $v) {
    if (substr($k, 0, 11) == 'EZ_GRIDLINE') {
        $userConstants[$k] = $v;
    }
}

$pdf->ezNewPage();
// title for advanced grid line output
$pdf->ezText("\n<b>GRIDLINE options</b>", 12);
$pdf->ezText("<b>using 'gridline' option - available in version >= 0.12-rc11</b>", 10);

$j = 0;
for ($i = EZ_GRIDLINE_ALL; $i >= 0; --$i) {
    if (!($j % 5) && $j != 0) {
        $pdf->ezNewPage();
    }

    $constName = '';
    if (($m = array_search($i, $userConstants))) {
        $constName = $m;
    }

    $title = sprintf('Bitmask: %05b | Integer: %d %s', $i, $i, $constName);

    $pdf->ezText("\n".$title."\n");
    $pdf->ezTable($data, $cols, '', ['showHeadings' => 1, 'shaded' => 1, 'gridlines' => $i, 'cols' => $coloptions, 'innerLineThickness' => 0.5, 'outerLineThickness' => 3]);
    ++$j;
}

$pdf->ezText("\n<b>SHADING options</b>", 12);

$pdf->ezText("\nColumn shading\n", 10);
$pdf->ezTable($data, ['type' => '', 'name' => ''], '', array('showHeadings' => 0, 'showBgCol' => 1, 'width' => 400, 'cols' => array(
                    'name' => ['bgcolor' => [0.2, 0.2, 0.4]], 'type' => ['bgcolor' => [0.4, 0.6, 0.6]],
                  ),
        ));

$pdf->ezText("\nHeader shading <b>since 0.12-rc9</b>\n");
$pdf->ezTable($data, $cols, '', ['shadeHeadingCol' => [0.4, 0.6, 0.6], 'width' => 400]);

$pdf->ezText("\nColored columns and a header\n");
$pdf->ezTable($data, $cols, '', [
            'showHeadings' => 1, 'showBgCol' => 1, 'width' => 400,
            'shadeHeadingCol' => [0.6, 0.6, 0.5],
            'cols' => [
                'name' => ['bgcolor' => [0.9, 0.9, 0.7]],
                'type' => ['bgcolor' => [0.6, 0.4, 0.2]],
            ]
        ]);

$pdf->ezText("\nJustified table and columns and row shading\n");
$pdf->ezTable($data, $cols, '', array('width' => 300,
         'shaded' => 2,
         'shadeCol' => [0.9, 0.9, 0.7],
         'shadeCol2' => [0.6, 0.4, 0.2],
         'shadeHeadingCol' => [0.6, 0.6, 0.5], 'cols' => ['type' => ['justification' => 'right'], 'name' => ['width' => 100]],
         ));

if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream();
}
